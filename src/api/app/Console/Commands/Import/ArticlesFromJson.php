<?php

namespace App\Console\Commands\Import;

use Backpack\Articles\app\Models\Article;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ParabellumKoval\BackpackImages\Services\ImageUploader;

class ArticlesFromJson extends Command
{
    protected $signature = 'import:articles-from-json {path=public/blog.json : Relative path to the WordPress JSON export}'
        . ' {--lang=cs : Language code that will be stored on imported articles}'
        . ' {--dry-run : Parse the file without writing changes to the database}';

    protected $description = 'Import blog articles that were exported from WordPress as JSON';

    private ImageUploader $imageUploader;

    public function __construct(ImageUploader $imageUploader)
    {
        parent::__construct();

        $this->imageUploader = $imageUploader;
    }

    public function handle(): int
    {
        $pathArgument = (string) $this->argument('path');
        $filePath = base_path($pathArgument);

        if (! File::exists($filePath)) {
            $this->error("JSON file not found: {$filePath}");

            return self::FAILURE;
        }

        $rawContents = File::get($filePath);
        $decoded = json_decode($rawContents, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            $this->error('Unable to decode JSON: ' . json_last_error_msg());

            return self::FAILURE;
        }

        $posts = array_values(array_filter($decoded, fn ($item) => is_array($item)));

        if (count($posts) === 0) {
            $this->warn('The provided JSON file does not contain any posts to import.');

            return self::SUCCESS;
        }

        $lang = (string) ($this->option('lang') ?? 'cs');
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run mode enabled — database will remain unchanged.');
        }

        $stats = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        $warnings = [];

        $progress = $this->output->createProgressBar(count($posts));
        $progress->start();

        foreach ($posts as $payload) {
            $result = $this->processArticle($payload, $lang, $dryRun);

            $stats[$result['status']] = ($stats[$result['status']] ?? 0) + 1;

            if ($result['message'] !== null) {
                $warnings[] = $result['message'];
            }

            $progress->advance();
        }

        $progress->finish();
        $this->newLine(2);

        $this->info(sprintf(
            'Processed %d posts. Created: %d, Updated: %d, Skipped: %d.',
            array_sum($stats),
            $stats['created'],
            $stats['updated'],
            $stats['skipped']
        ));

        if ($dryRun) {
            $this->info('Dry run completed — no records were persisted.');
        }

        if ($warnings !== []) {
            $this->warn('Warnings:');
            foreach ($warnings as $warning) {
                $this->line("  - {$warning}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return array{status: string, message: string|null}
     */
    private function processArticle(array $payload, string $lang, bool $dryRun): array
    {
        $title = $this->extractTitle($payload);
        $slug = $this->extractSlug($payload, $title);

        if ($slug === '') {
            return [
                'status' => 'skipped',
                'message' => sprintf('Skipped WordPress post #%s — slug is empty.', Arr::get($payload, 'id', '?')),
            ];
        }

        $warnings = [];
        $uploadCache = [];

        $contentResult = $this->sanitizeContentHtml($payload, $title, $dryRun, $uploadCache, $warnings);
        $content = $contentResult['html'];
        $contentImages = $contentResult['images'];

        $excerpt = $this->sanitizeExcerpt(Arr::get($payload, 'excerpt.rendered'));
        $seo = $this->extractSeo($payload);
        $metaImages = $this->extractImages($payload, $title, $dryRun, $uploadCache, $warnings);
        $images = $this->mergeImages($contentImages, $metaImages);
        $extras = $this->extractExtras($payload);
        $publishedAt = $this->extractPublishedAt($payload);
        $status = $this->mapStatus(Arr::get($payload, 'status'));

        $attributes = [
            'lang' => $lang,
            'title' => $title !== '' ? $title : $slug,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $excerpt !== '' ? $excerpt : null,
            'status' => $status,
            'published_at' => $publishedAt,
            'seo' => $seo,
            'images' => $images,
            'extras' => $extras,
        ];

        $sourceHash = $this->computeSourceHash($attributes);
        $attributes['extras']['source_hash'] = $sourceHash;
        $attributes['extras']['source_synced_at'] = Carbon::now()->toISOString();

        $existing = $this->findExistingArticle($payload, $slug, $lang);

        if ($existing !== null) {
            $currentHash = Arr::get($existing->extras, 'source_hash');

            if ($currentHash === $sourceHash) {
                return [
                    'status' => 'skipped',
                    'message' => sprintf('No changes detected for article with slug "%s".', $slug),
                ];
            }

            if ($attributes['excerpt'] === null) {
                $attributes['excerpt'] = $existing->excerpt;
            }

            if ($attributes['seo'] === [] && is_array($existing->seo)) {
                $attributes['seo'] = $existing->seo;
            }

            if ($attributes['images'] === [] && is_array($existing->images)) {
                $attributes['images'] = $existing->images;
            }

            $attributes['extras'] = array_replace_recursive(
                (array) $existing->extras,
                $attributes['extras']
            );
        }

        if ($dryRun) {
            $message = $existing
                ? sprintf('Dry run — would update article with slug "%s".', $slug)
                : sprintf('Dry run — would create article with slug "%s".', $slug);

            if ($warnings !== []) {
                $message .= ' Warnings: ' . implode(' | ', $warnings);
            }

            return [
                'status' => $existing ? 'updated' : 'created',
                'message' => $message,
            ];
        }

        $article = $existing ?? new Article();
        $article->fill($attributes);
        $article->save();

        return [
            'status' => $existing ? 'updated' : 'created',
            'message' => $warnings !== [] ? implode(' | ', $warnings) : null,
        ];
    }

    private function extractTitle(array $payload): string
    {
        $rawTitle = (string) Arr::get($payload, 'title.rendered', '');

        if ($rawTitle === '') {
            return '';
        }

        $cleanTitle = strip_tags($rawTitle);

        return html_entity_decode($cleanTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function extractSlug(array $payload, string $fallbackTitle): string
    {
        $slug = (string) Arr::get($payload, 'slug', '');

        if ($slug !== '') {
            return $slug;
        }

        $generated = Str::slug($fallbackTitle);

        if ($generated !== '') {
            return $generated;
        }

        $id = Arr::get($payload, 'id');

        return $id ? 'wp-' . $id : '';
    }

    private function extractPublishedAt(array $payload): ?Carbon
    {
        $date = Arr::get($payload, 'date_gmt') ?? Arr::get($payload, 'date');

        if (! $date) {
            return null;
        }

        try {
            $timestamp = Carbon::parse($date);

            return $timestamp->setTimezone(config('app.timezone', 'UTC'));
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function mapStatus(?string $status): string
    {
        return Str::lower((string) $status) === 'publish' ? 'PUBLISHED' : 'DRAFT';
    }

    private function sanitizeExcerpt(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = trim($decoded);

        if ($decoded === '') {
            return '';
        }

        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><blockquote><pre><code><h1><h2><h3><h4><h5><h6>';
        $stripped = strip_tags($decoded, $allowedTags);

        return trim($stripped);
    }

    /**
     * @return array{html: string, images: array<array<string, mixed>>}
     */
    private function sanitizeContentHtml(array $payload, string $title, bool $dryRun, array &$uploadCache, array &$warnings): array
    {
        $raw = Arr::get($payload, 'content.rendered');
        $decoded = html_entity_decode((string) $raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = trim($decoded);

        if ($decoded === '') {
            return [
                'html' => '',
                'images' => [],
            ];
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorLevel = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<?xml encoding="utf-8"?><div>' . $decoded . '</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );
        } catch (\Throwable $exception) {
            $warnings[] = sprintf(
                'Failed to sanitize article body for slug "%s": %s',
                Arr::get($payload, 'slug', '?'),
                $exception->getMessage()
            );

            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorLevel);

            return [
                'html' => strip_tags($decoded),
                'images' => [],
            ];
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorLevel);

        $wrapper = $document->getElementsByTagName('div')->item(0);

        if (! $wrapper instanceof DOMElement) {
            return [
                'html' => strip_tags($decoded),
                'images' => [],
            ];
        }

        $this->removeNodesByTagNames($wrapper, [
            'style',
            'script',
            'link',
            'iframe',
            'object',
            'embed',
            'noscript',
            'form',
            'input',
            'button',
            'canvas',
        ]);

        $this->removeCommentsFromDocument($document);

        $allowedTags = $this->allowedHtmlTags();
        $imageRecords = [];

        $elements = [];

        foreach ($wrapper->getElementsByTagName('*') as $element) {
            if ($element instanceof DOMElement) {
                $elements[] = $element;
            }
        }

        foreach ($elements as $element) {
            $tag = Str::lower($element->tagName);

            if (! array_key_exists($tag, $allowedTags)) {
                $this->unwrapNode($element);

                continue;
            }

            $this->stripDisallowedAttributes($element, $allowedTags[$tag]);

            if ($tag === 'a') {
                $this->sanitizeAnchor($element);
            }

            if ($tag === 'img') {
                $record = $this->sanitizeImageElement($element, $payload, $title, $dryRun, $uploadCache, $warnings);

                if ($record === null) {
                    $this->removeNode($element);

                    continue;
                }

                $imageRecords[] = $record;
            }
        }

        $html = $this->getInnerHtml($wrapper);

        return [
            'html' => trim($html),
            'images' => $imageRecords,
        ];
    }

    private function extractSeo(array $payload): array
    {
        $metaTitle = $this->normalizeText(Arr::get($payload, 'yoast_head_json.title'));
        $metaDescription = $this->normalizeText(Arr::get($payload, 'yoast_head_json.description'));

        $seo = [
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
        ];

        return $this->filterArray($seo);
    }

    private function extractImages(array $payload, string $title, bool $dryRun, array &$uploadCache, array &$warnings): array
    {
        $images = [];
        $ogImages = Arr::get($payload, 'yoast_head_json.og_image', []);

        foreach ($ogImages as $image) {
            $url = Arr::get($image, 'url');

            if (! is_string($url) || $url === '') {
                continue;
            }

            $resolved = $this->resolveImageUrl($url, $payload);

            if ($resolved === null) {
                $warnings[] = sprintf('Skipped OG image with unsupported src "%s".', is_scalar($url) ? (string) $url : '[invalid type]');

                continue;
            }

            $alt = Arr::get($image, 'alt') ?? Arr::get($image, 'alt_text') ?? $title;
            $record = $this->storeImageFromUrl($resolved, is_string($alt) ? $alt : $title, $dryRun, $uploadCache, $warnings);

            if ($record !== null) {
                $images[] = $record['record'];
            }
        }

        $graph = Arr::get($payload, 'yoast_head_json.schema.@graph', []);

        foreach ($graph as $node) {
            if (! is_array($node)) {
                continue;
            }

            $thumbnail = Arr::get($node, 'thumbnailUrl');

            if (! is_string($thumbnail) || $thumbnail === '') {
                continue;
            }

            $resolved = $this->resolveImageUrl($thumbnail, $payload);

            if ($resolved === null) {
                $warnings[] = sprintf('Skipped schema image with unsupported src "%s".', is_scalar($thumbnail) ? (string) $thumbnail : '[invalid type]');

                continue;
            }

            $record = $this->storeImageFromUrl($resolved, $title, $dryRun, $uploadCache, $warnings);

            if ($record !== null) {
                $images[] = $record['record'];
            }
        }

        return $images;
    }

    private function buildImageRecord(string $source, ?string $alt = null, bool $isStoredPath = false): array
    {
        $normalizedAlt = $this->normalizeAltText($alt);
        $normalizedSource = $isStoredPath ? ltrim($source, '/') : trim($source);

        return $this->filterArray([
            'src' => $normalizedSource,
            'alt' => $normalizedAlt,
            'title' => $normalizedAlt,
        ]);
    }

    private function mergeImages(array ...$groups): array
    {
        $unique = [];

        foreach ($groups as $images) {
            foreach ($images as $image) {
                if (! is_array($image)) {
                    continue;
                }

                $src = Arr::get($image, 'src');

                if (! is_string($src) || $src === '') {
                    continue;
                }

                $unique[$src] = $image;
            }
        }

        return array_values($unique);
    }

    private function sanitizeImageElement(
        DOMElement $element,
        array $payload,
        string $fallbackAlt,
        bool $dryRun,
        array &$uploadCache,
        array &$warnings
    ): ?array {
        $src = $element->getAttribute('src');
        $resolved = $this->resolveImageUrl($src, $payload);

        if ($resolved === null) {
            if ($src !== '') {
                $warnings[] = sprintf('Skipped inline image with unsupported src "%s".', $src);
            }

            return null;
        }

        $altAttribute = $element->getAttribute('alt');
        $alt = $altAttribute !== '' ? $altAttribute : $fallbackAlt;

        $record = $this->storeImageFromUrl($resolved, $alt, $dryRun, $uploadCache, $warnings);

        if ($record === null) {
            return null;
        }

        $normalizedAlt = $record['record']['alt'] ?? null;

        $element->setAttribute('src', $record['url']);

        if ($normalizedAlt !== null) {
            $element->setAttribute('alt', $normalizedAlt);
            $element->setAttribute('title', $normalizedAlt);
        } else {
            $element->removeAttribute('alt');
            $element->removeAttribute('title');
        }

        return $record['record'];
    }

    /**
     * @return array{record: array<string, mixed>, url: string}|null
     */
    private function storeImageFromUrl(
        string $url,
        ?string $alt,
        bool $dryRun,
        array &$uploadCache,
        array &$warnings
    ): ?array {
        $normalizedUrl = trim($url);

        if ($normalizedUrl === '') {
            return null;
        }

        $cacheKey = mb_strtolower($normalizedUrl);

        if (isset($uploadCache[$cacheKey])) {
            return $uploadCache[$cacheKey];
        }

        if ($dryRun) {
            $record = [
                'record' => $this->buildImageRecord($normalizedUrl, $alt, false),
                'url' => $normalizedUrl,
            ];

            $uploadCache[$cacheKey] = $record;

            return $record;
        }

        try {
            $stored = $this->imageUploader->upload($normalizedUrl, Article::imageUploadOptions());
        } catch (\Throwable $exception) {
            $warnings[] = sprintf('Failed to upload image %s: %s', $normalizedUrl, $exception->getMessage());

            return null;
        }

        $record = [
            'record' => $this->buildImageRecord($stored->path, $alt, true),
            'url' => $stored->url,
        ];

        $uploadCache[$cacheKey] = $record;

        return $record;
    }

    private function sanitizeAnchor(DOMElement $element): void
    {
        if (! $element->hasAttribute('href')) {
            return;
        }

        $href = html_entity_decode($element->getAttribute('href'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $href = trim($href);

        if ($href === '') {
            $element->removeAttribute('href');

            return;
        }

        if (Str::startsWith(Str::lower($href), 'javascript:')) {
            $element->removeAttribute('href');

            return;
        }

        if (Str::startsWith($href, '//')) {
            $href = 'https:' . $href;
        }

        $parsed = parse_url($href);

        if ($parsed === false) {
            $element->removeAttribute('href');

            return;
        }

        if (isset($parsed['scheme'])) {
            if (! in_array(Str::lower($parsed['scheme']), ['http', 'https', 'mailto', 'tel'], true)) {
                $element->removeAttribute('href');

                return;
            }
        } elseif (! Str::startsWith($href, '/') && ! Str::startsWith($href, '#')) {
            $href = '/' . ltrim($href, '/');
        }

        $element->setAttribute('href', $href);
    }

    private function stripDisallowedAttributes(DOMElement $element, array $allowed): void
    {
        if (! $element->hasAttributes()) {
            return;
        }

        $allowedLookup = array_flip(array_map('strtolower', $allowed));

        for ($index = $element->attributes->length - 1; $index >= 0; $index--) {
            $attribute = $element->attributes->item($index);

            if (! $attribute) {
                continue;
            }

            $name = strtolower($attribute->nodeName);

            if (! array_key_exists($name, $allowedLookup)) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            $value = html_entity_decode($attribute->nodeValue, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $value = trim($value);

            if ($value === '') {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            $element->setAttribute($attribute->nodeName, $value);
        }
    }

    private function resolveImageUrl(string $value, array $payload): ?string
    {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = trim($decoded);

        if ($decoded === '' || Str::startsWith(Str::lower($decoded), ['data:', 'javascript:', 'blob:'])) {
            return null;
        }

        if (Str::startsWith($decoded, '//')) {
            $decoded = 'https:' . $decoded;
        }

        if (! preg_match('#^https?://#i', $decoded)) {
            $base = Arr::get($payload, 'link') ?? Arr::get($payload, 'guid.rendered');

            if (! is_string($base) || $base === '') {
                return null;
            }

            $absolute = $this->buildAbsoluteUrl($base, $decoded);

            if ($absolute === null) {
                return null;
            }

            $decoded = $absolute;
        }

        if (! preg_match('#^https?://#i', $decoded)) {
            return null;
        }

        return $decoded;
    }

    private function buildAbsoluteUrl(string $base, string $relative): ?string
    {
        $baseParts = parse_url($base);

        if ($baseParts === false || ! isset($baseParts['scheme'], $baseParts['host'])) {
            return null;
        }

        $scheme = $baseParts['scheme'];
        $host = $baseParts['host'];
        $port = isset($baseParts['port']) ? ':' . $baseParts['port'] : '';
        $basePath = $baseParts['path'] ?? '/';

        $relativeParts = parse_url($relative);

        $relativePath = $relativeParts['path'] ?? '';
        $relativeQuery = isset($relativeParts['query']) ? '?' . $relativeParts['query'] : '';
        $relativeFragment = isset($relativeParts['fragment']) ? '#' . $relativeParts['fragment'] : '';

        if ($relative === '' || $relativePath === '') {
            $path = $basePath;
        } elseif (Str::startsWith($relativePath, '/')) {
            $path = $relativePath;
        } else {
            $baseSegments = array_values(array_filter(explode('/', trim($basePath, '/')), fn ($segment) => $segment !== ''));

            if (! Str::endsWith($basePath, '/')) {
                array_pop($baseSegments);
            }

            $segments = explode('/', $relativePath);

            foreach ($segments as $segment) {
                if ($segment === '' || $segment === '.') {
                    continue;
                }

                if ($segment === '..') {
                    array_pop($baseSegments);

                    continue;
                }

                $baseSegments[] = $segment;
            }

            $path = '/' . implode('/', $baseSegments);
        }

        $path = preg_replace('#/+#', '/', '/' . ltrim($path, '/'));

        return sprintf('%s://%s%s%s%s%s', $scheme, $host, $port, $path, $relativeQuery, $relativeFragment);
    }

    private function allowedHtmlTags(): array
    {
        return [
            'p' => [],
            'br' => [],
            'strong' => [],
            'b' => [],
            'em' => [],
            'i' => [],
            'u' => [],
            'ul' => [],
            'ol' => [],
            'li' => [],
            'blockquote' => [],
            'pre' => [],
            'code' => [],
            'h1' => [],
            'h2' => [],
            'h3' => [],
            'h4' => [],
            'h5' => [],
            'h6' => [],
            'hr' => [],
            'a' => ['href'],
            'img' => ['src', 'alt', 'title'],
        ];
    }

    private function removeNodesByTagNames(DOMElement $root, array $tags): void
    {
        foreach ($tags as $tag) {
            $nodeList = $root->getElementsByTagName($tag);

            while ($nodeList->length > 0) {
                $node = $nodeList->item(0);

                if (! $node instanceof DOMNode) {
                    break;
                }

                $this->removeNode($node);
            }
        }
    }

    private function removeCommentsFromDocument(DOMDocument $document): void
    {
        $xpath = new DOMXPath($document);

        foreach ($xpath->query('//comment()') as $comment) {
            if ($comment instanceof DOMNode && $comment->parentNode !== null) {
                $comment->parentNode->removeChild($comment);
            }
        }
    }

    private function getInnerHtml(DOMElement $element): string
    {
        $html = '';

        foreach ($element->childNodes as $child) {
            $html .= $element->ownerDocument?->saveHTML($child) ?? '';
        }

        return $html;
    }

    private function unwrapNode(DOMNode $node): void
    {
        $parent = $node->parentNode;

        if (! $parent) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    private function removeNode(DOMNode $node): void
    {
        $parent = $node->parentNode;

        if ($parent !== null) {
            $parent->removeChild($node);
        }
    }

    private function normalizeAltText(?string $value): ?string
    {
        $normalized = $this->normalizeText($value);

        if ($normalized === null) {
            return null;
        }

        $collapsed = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return $collapsed === '' ? null : Str::limit($collapsed, 160);
    }


    private function extractExtras(array $payload): array
    {
        $wpData = [
            'id' => Arr::get($payload, 'id'),
            'guid' => Arr::get($payload, 'guid.rendered'),
            'link' => Arr::get($payload, 'link'),
            'type' => Arr::get($payload, 'type'),
            'status' => Arr::get($payload, 'status'),
            'author' => Arr::get($payload, 'author'),
            'featured_media' => Arr::get($payload, 'featured_media'),
            'categories' => Arr::get($payload, 'categories', []),
            'tags' => Arr::get($payload, 'tags', []),
            'class_list' => Arr::get($payload, 'class_list', []),
            'comment_status' => Arr::get($payload, 'comment_status'),
            'ping_status' => Arr::get($payload, 'ping_status'),
            'template' => Arr::get($payload, 'template'),
        ];

        $yoast = [
            'canonical' => Arr::get($payload, 'yoast_head_json.canonical'),
            'og_locale' => Arr::get($payload, 'yoast_head_json.og_locale'),
            'og_type' => Arr::get($payload, 'yoast_head_json.og_type'),
            'og_title' => Arr::get($payload, 'yoast_head_json.og_title'),
            'og_description' => Arr::get($payload, 'yoast_head_json.og_description'),
            'article_published_time' => Arr::get($payload, 'yoast_head_json.article_published_time'),
            'article_modified_time' => Arr::get($payload, 'yoast_head_json.article_modified_time'),
            'twitter_card' => Arr::get($payload, 'yoast_head_json.twitter_card'),
            'author' => Arr::get($payload, 'yoast_head_json.author'),
        ];

        return $this->filterArray([
            'source' => 'wordpress',
            'wp' => $this->filterArray($wpData),
            'meta' => Arr::get($payload, 'meta') ?? [],
            'yoast' => $this->filterArray($yoast),
            'yoast_head' => Arr::get($payload, 'yoast_head'),
        ]);
    }

    private function findExistingArticle(array $payload, string $slug, string $lang): ?Article
    {
        $wpId = Arr::get($payload, 'id');

        if ($wpId) {
            $byWpId = Article::query()
                ->where('lang', $lang)
                ->where('extras->wp->id', $wpId)
                ->first();

            if ($byWpId !== null) {
                return $byWpId;
            }
        }

        return Article::query()
            ->where('lang', $lang)
            ->where('slug', $slug)
            ->first();
    }

    private function computeSourceHash(array $attributes): string
    {
        $payload = [
            'lang' => $attributes['lang'],
            'title' => $attributes['title'],
            'slug' => $attributes['slug'],
            'content' => $attributes['content'],
            'excerpt' => $attributes['excerpt'],
            'status' => $attributes['status'],
            'published_at' => $attributes['published_at'] instanceof Carbon
                ? $attributes['published_at']->toISOString()
                : $attributes['published_at'],
            'seo' => $attributes['seo'],
            'images' => $attributes['images'],
            'extras' => Arr::only($attributes['extras'], ['wp', 'yoast', 'meta']),
        ];

        return md5(json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    private function normalizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $decoded = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $cleaned = trim($decoded);

        return $cleaned === '' ? null : $cleaned;
    }

    private function filterArray(array $data): array
    {
        return array_filter($data, function ($value) {
            if (is_array($value)) {
                return $value !== [] && $value !== null;
            }

            return $value !== null && $value !== '';
        });
    }
}
