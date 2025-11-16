<?php

namespace App\Console\Commands\Import;

use Backpack\Articles\app\Models\Article;
use Backpack\Tag\app\Models\Tag;
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
        . ' {--countries= : Comma-separated list of country codes (e.g., cz,ua,de)}'
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
        $countries = $this->parseCountries((string) $this->option('countries'));

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
            $result = $this->processArticle($payload, $lang, $countries, $dryRun);

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
    private function processArticle(array $payload, string $lang, array $countries, bool $dryRun): array
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
        $readingTimeMinutes = $this->estimateReadingTimeMinutes($content);

        $excerpt = $this->sanitizeExcerpt(Arr::get($payload, 'excerpt.rendered'));
        $seo = $this->extractSeo($payload);
        $metaImages = $this->extractImages($payload, $title, $dryRun, $uploadCache, $warnings);
        
        // Only save the first image (featured image) to the images field
        $images = !empty($contentImages) ? [reset($contentImages)] : (!empty($metaImages) ? [reset($metaImages)] : []);
        
        $extras = $this->extractExtras($payload);
        if ($readingTimeMinutes !== null) {
            $extras['reading_time_minutes'] = $readingTimeMinutes;
        }
        
        $countriesForArticle = $countries !== [] ? array_values($countries) : null;
        $tagTexts = $this->extractTagTexts($payload);

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
            'countries' => $countriesForArticle,
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

            if ($countries === []) {
                $attributes['countries'] = $existing->countries;
            }
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
        $this->syncArticleTags($article, $tagTexts, $lang, $warnings);

        return [
            'status' => $existing ? 'updated' : 'created',
            'message' => $warnings !== [] ? implode(' | ', $warnings) : null,
        ];
    }

    private function estimateReadingTimeMinutes(string $html): ?int
    {
        $text = strip_tags($html);
        $text = preg_replace('/\s+/u', ' ', (string) $text);
        $text = trim((string) $text);

        if ($text === '') {
            return null;
        }

        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = is_array($words) ? count($words) : 0;

        if ($wordCount === 0) {
            return null;
        }

        $minutes = (int) ceil($wordCount / 200);

        return max($minutes, 1);
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

        $removedGrids = $this->removeImprezaGrids($document);
        if ($removedGrids > 0) {
            $warnings[] = sprintf('Removed %d Impreza/UpSolution grid(s) from body.', $removedGrids);
        }

        $allowedTags = $this->allowedHtmlTags();
        $featuredImageRecord = null;
        $isFirstImage = true;

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
                if ($isFirstImage) {
                    // First image: upload via ImageUploader and extract from content
                    $record = $this->sanitizeFeaturedImageElement($element, $payload, $title, $dryRun, $uploadCache, $warnings);
                    
                    if ($record !== null) {
                        $featuredImageRecord = $record;
                        $isFirstImage = false;
                    }
                    
                    // Remove first image from content
                    $this->removeNode($element);
                } else {
                    // Other images: upload to public/uploads
                    $uploaded = $this->sanitizeContentImageElement($element, $payload, $title, $dryRun, $warnings);
                    
                    if ($uploaded === false) {
                        $this->removeNode($element);
                    }
                }
            }
        }

        $html = $this->getInnerHtml($wrapper);

        return [
            'html' => trim($html),
            'images' => $featuredImageRecord !== null ? [$featuredImageRecord] : [],
        ];
    }

    /**
     * Удаляет импортизированные из WP «слайдеры/карусели» от Impreza/UpSolution:
     *  - любые контейнеры с классом w-grid (в т.ч. prod-slider)
     *  - любые контейнеры с id, начинающимся на us_grid_
     * Возвращает количество удалённых контейнеров.
     */
    private function removeImprezaGrids(DOMDocument $document): int
    {
        $xpath = new DOMXPath($document);

        // Находим все контейнеры грида. Берём union двух критериев:
        // 1) .w-grid (импрезовский грид)
        // 2) id="us_grid_*" (их же айдишники)
        $nodes = $xpath->query(
            '//*[contains(concat(" ", normalize-space(@class), " "), " w-grid ")]' .
            ' | ' .
            '//*[starts-with(@id, "us_grid_")]'
        );

        if (! $nodes || $nodes->length === 0) {
            return 0;
        }

        // Сначала запомним id, чтобы подчистить <style> с такими селекторами (на всякий случай)
        $gridIds = [];
        foreach ($nodes as $el) {
            /** @var DOMElement $el */
            if ($el->hasAttribute('id')) {
                $gridIds[] = $el->getAttribute('id');
            }
        }

        // Удаляем сами контейнеры целиком (со всем содержимым)
        $removed = 0;
        // Важно: NodeList «живой», поэтому удаляем в обратном порядке через массив-копию
        $toDelete = [];
        foreach ($nodes as $n) { $toDelete[] = $n; }
        foreach ($toDelete as $n) {
            if ($n->parentNode) {
                $n->parentNode->removeChild($n);
                $removed++;
            }
        }

        // Подчистим любые <style>, в которых встречается #us_grid_X
        if ($gridIds !== []) {
            $styleNodes = $xpath->query('//style');
            if ($styleNodes && $styleNodes->length) {
                $toDeleteStyles = [];
                foreach ($styleNodes as $style) {
                    $css = $style->nodeValue ?? '';
                    foreach ($gridIds as $id) {
                        if ($css !== '' && strpos($css, '#'.$id) !== false) {
                            $toDeleteStyles[] = $style;
                            break;
                        }
                    }
                }
                foreach ($toDeleteStyles as $style) {
                    if ($style->parentNode) {
                        $style->parentNode->removeChild($style);
                    }
                }
            }
        }

        return $removed;
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
     * Process first image (featured) - upload via ImageUploader
     */
    private function sanitizeFeaturedImageElement(
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
                $warnings[] = sprintf('Skipped featured image with unsupported src "%s".', $src);
            }

            return null;
        }

        $altAttribute = $element->getAttribute('alt');
        $alt = $altAttribute !== '' ? $altAttribute : $fallbackAlt;

        $record = $this->storeImageFromUrl($resolved, $alt, $dryRun, $uploadCache, $warnings);

        if ($record === null) {
            return null;
        }

        return $record['record'];
    }

    /**
     * Process content images (non-featured) - upload to public/uploads directly
     * 
     * @return bool True if processed successfully, false if should be removed
     */
    private function sanitizeContentImageElement(
        DOMElement $element,
        array $payload,
        string $fallbackAlt,
        bool $dryRun,
        array &$warnings
    ): bool {
        $src = $element->getAttribute('src');
        $resolved = $this->resolveImageUrl($src, $payload);

        if ($resolved === null) {
            if ($src !== '') {
                $warnings[] = sprintf('Skipped content image with unsupported src "%s".', $src);
            }

            return false;
        }

        $altAttribute = $element->getAttribute('alt');
        $alt = $altAttribute !== '' ? $altAttribute : $fallbackAlt;

        $uploaded = $this->uploadContentImage($resolved, $alt, $dryRun, $warnings);

        if ($uploaded === null) {
            return false;
        }

        $element->setAttribute('src', $uploaded['url']);

        if ($uploaded['alt'] !== null) {
            $element->setAttribute('alt', $uploaded['alt']);
            $element->setAttribute('title', $uploaded['alt']);
        } else {
            $element->removeAttribute('alt');
            $element->removeAttribute('title');
        }

        return true;
    }

    /**
     * Upload content image directly to public/uploads
     * 
     * @return array{url: string, alt: string|null}|null
     */
    private function uploadContentImage(string $url, ?string $alt, bool $dryRun, array &$warnings): ?array
    {
        $normalizedUrl = trim($url);

        if ($normalizedUrl === '') {
            return null;
        }

        if ($dryRun) {
            return [
                'url' => $normalizedUrl,
                'alt' => $this->normalizeAltText($alt),
            ];
        }

        try {
            // Download image
            $imageContent = @file_get_contents($normalizedUrl);
            
            if ($imageContent === false) {
                $warnings[] = sprintf('Failed to download content image: %s', $normalizedUrl);
                return null;
            }

            // Generate filename
            $extension = pathinfo(parse_url($normalizedUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            if ($extension === '' || strlen($extension) > 5) {
                $extension = 'jpg';
            }
            
            $filename = md5($normalizedUrl . time()) . '.' . $extension;
            $uploadDir = public_path('uploads');
            
            // Create directory if not exists
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }
            
            $filePath = $uploadDir . '/' . $filename;
            
            // Save file
            if (file_put_contents($filePath, $imageContent) === false) {
                $warnings[] = sprintf('Failed to save content image: %s', $normalizedUrl);
                return null;
            }

            return [
                'url' => '/uploads/' . $filename,
                'alt' => $this->normalizeAltText($alt),
            ];
        } catch (\Throwable $exception) {
            $warnings[] = sprintf('Failed to upload content image %s: %s', $normalizedUrl, $exception->getMessage());
            return null;
        }
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

    /**
     * @return array<int, string>
     */
    private function extractTagTexts(array $payload): array
    {
        $candidates = [];

        $directSections = Arr::get($payload, 'yoast_head_json.articleSection');
        if ($directSections !== null) {
            $candidates[] = $directSections;
        }

        $schema = Arr::get($payload, 'yoast_head_json.schema');
        if (is_array($schema)) {
            if (array_key_exists('articleSection', $schema)) {
                $candidates[] = $schema['articleSection'];
            }

            $graphNodes = Arr::get($schema, '@graph', []);
            if (is_array($graphNodes)) {
                foreach ($graphNodes as $node) {
                    if (is_array($node) && array_key_exists('articleSection', $node)) {
                        $candidates[] = $node['articleSection'];
                    }
                }
            }
        }

        $tags = [];

        foreach ($candidates as $candidate) {
            foreach ($this->normalizeTagCandidate($candidate) as $tag) {
                $tags[$tag] = true;
            }
        }

        return array_keys($tags);
    }

    /**
     * @param mixed $candidate
     * @return array<int, string>
     */
    private function normalizeTagCandidate($candidate): array
    {
        if ($candidate === null) {
            return [];
        }

        if (is_string($candidate)) {
            $candidate = [$candidate];
        }

        if (! is_array($candidate)) {
            return [];
        }

        $normalized = [];

        foreach ($candidate as $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                foreach ($this->normalizeTagCandidate($value) as $nested) {
                    $normalized[$nested] = true;
                }

                continue;
            }

            if (! is_scalar($value)) {
                continue;
            }

            $fragments = preg_split('/[,;]+/u', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
            if ($fragments === false || $fragments === []) {
                $fragments = [(string) $value];
            }

            foreach ($fragments as $fragment) {
                $label = Str::lower(trim((string) $fragment));

                if ($label === '') {
                    continue;
                }

                $normalized[$label] = true;
            }
        }

        return array_keys($normalized);
    }

    private function syncArticleTags(Article $article, array $tagTexts, string $lang, array &$warnings): void
    {
        $normalizedTags = [];

        foreach ($tagTexts as $tagText) {
            $label = Str::lower(trim($tagText));

            if ($label === '') {
                continue;
            }

            $value = $this->generateTagValue($label);

            if (! array_key_exists($value, $normalizedTags)) {
                $normalizedTags[$value] = [
                    'value' => $value,
                    'label' => $label,
                ];
            }
        }

        if ($normalizedTags === []) {
            $article->tags()->sync([]);

            return;
        }

        $tagIds = [];

        foreach ($normalizedTags as $tagData) {
            try {
                $tag = Tag::query()->where('value', $tagData['value'])->first();

                if ($tag === null) {
                    $tag = new Tag();
                    $tag->value = $tagData['value'];
                    $tag->color = $this->generateTagColor($tagData['label']);
                    $tag->setTranslations('label', [$lang => $tagData['label']]);
                    $tag->save();
                } else {
                    $currentTranslation = $tag->getTranslation('label', $lang, false);

                    if ($currentTranslation === null || $currentTranslation === '') {
                        $tag->setTranslation('label', $lang, $tagData['label']);
                        $tag->save();
                    }
                }

                $tagIds[] = $tag->id;
            } catch (\Throwable $exception) {
                $warnings[] = sprintf(
                    'Failed to ensure tag "%s": %s',
                    $tagData['label'],
                    $exception->getMessage()
                );
            }
        }

        if ($tagIds === []) {
            return;
        }

        $article->tags()->sync($tagIds);
    }

    private function generateTagValue(string $label): string
    {
        $value = Str::slug($label);

        if ($value === '') {
            $value = Str::slug(Str::ascii($label));
        }

        if ($value === '') {
            $value = substr(md5($label), 0, 12);
        }

        return $value;
    }

    private function generateTagColor(string $tagText): string
    {
        $hash = md5($tagText);
        $primary = substr($hash, 0, 6);
        $primary = str_pad($primary, 6, '0');
        $components = str_split($primary, 2);

        if (count($components) < 3) {
            $components = array_pad($components, 3, '00');
        }

        $adjusted = array_map(static function (string $component): int {
            $value = hexdec($component);

            return max(70, $value);
        }, array_slice($components, 0, 3));

        return sprintf('#%02x%02x%02x', $adjusted[0], $adjusted[1], $adjusted[2]);
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
            'countries' => $attributes['countries'] ?? null,
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

    /**
     * Parse comma-separated country codes and return as lowercase array
     *
     * @param string $input
     * @return array
     */
    private function parseCountries(string $input): array
    {
        if ($input === '') {
            return [];
        }

        $codes = array_map('trim', explode(',', $input));
        $codes = array_map('strtolower', $codes);
        $codes = array_filter($codes, fn($code) => $code !== '');

        return array_values(array_unique($codes));
    }
}
