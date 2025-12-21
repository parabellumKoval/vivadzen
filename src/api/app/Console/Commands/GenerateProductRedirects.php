<?php

namespace App\Console\Commands;

use App\Models\Product;
use Backpack\Store\app\Models\Catalog;
use Backpack\Store\app\Services\Store as StoreService;
use Illuminate\Console\Command;

class GenerateProductRedirects extends Command
{
    protected const COMMAND_NAME = 'redirects:generate-cz-products';
    protected const COMMAND_DESCRIPTION = 'Generate redirects.csv for CZ product URLs';
    protected const DEFAULT_SOURCE = 'public/cz-product.php';
    protected const DEFAULT_OUTPUT = 'storage/app/redirects.csv';
    protected const DESTINATION_PREFIX = 'https://vivadzen.com/cz';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    public function __construct()
    {
        $this->signature = $this->buildSignature();
        $this->description = static::COMMAND_DESCRIPTION;

        parent::__construct();
    }

    protected function buildSignature(): string
    {
        return static::COMMAND_NAME . PHP_EOL .
            '                            {--source=' . static::DEFAULT_SOURCE . ' : Path to the PHP file that defines $productsUrls}' . PHP_EOL .
            '                            {--output=' . static::DEFAULT_OUTPUT . ' : Target CSV (relative to api base path by default)}';
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourcePath = $this->resolvePath((string) $this->option('source'));
        $outputPath = $this->resolvePath((string) $this->option('output'));

        if (! file_exists($sourcePath)) {
            $this->error("Source file not found at {$sourcePath}");
            return self::FAILURE;
        }

        $productUrls = $this->loadProductUrls($sourcePath);

        if (empty($productUrls)) {
            $this->error('No URLs were loaded from ' . $sourcePath);
            return self::FAILURE;
        }

        $redirectRows = [];
        $notFound = [];

        foreach ($productUrls as $url) {
            $slug = $this->extractSlug($url);
            $countryCode = $this->extractCountryCode($url);

            if (! $slug) {
                $this->warn("Unable to extract slug from {$url}");
                $notFound[] = $url;
                continue;
            }

            $product = $this->findProductBySlug($slug, $countryCode);

            if (! $product) {
                $this->warn("Product with slug {$slug} was not found ({$url})");
                $notFound[] = $url;
                continue;
            }

            $targetProduct = $product;
            $firstModification = $product->children()->orderBy('id')->select(['id', 'slug'])->first();

            if ($firstModification) {
                $targetProduct = $firstModification;
                $this->info("Found {$product->slug}, using modification {$targetProduct->slug}");
            } else {
                $this->info("Found {$product->slug}, using primary slug");
            }

            $destinationUrl = $this->buildDestinationUrl($targetProduct->slug);
            $redirectRows[] = [$url, $destinationUrl, 'true'];
            $this->line("Added redirect: {$url} -> {$destinationUrl}");
        }

        try {
            $this->writeCsv($outputPath, $redirectRows);
        } catch (\RuntimeException $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

        $this->info('Redirect file saved to ' . $outputPath);
        $this->info('Total redirects: ' . count($redirectRows));

        if (! empty($notFound)) {
            $this->newLine();
            $this->warn('URLs without matches:');
            foreach ($notFound as $missingUrl) {
                $this->line(' - ' . $missingUrl);
            }
        } else {
            $this->newLine();
            $this->info('All URLs were resolved successfully.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int,string>
     */
    private function loadProductUrls(string $filePath): array
    {
        $productsUrls = [];

        /** @psalm-suppress UnresolvableInclude */
        include $filePath;

        if (! is_array($productsUrls)) {
            return [];
        }

        return array_values(array_filter(
            $productsUrls,
            static fn ($url) => is_string($url) && $url !== ''
        ));
    }

    private function extractSlug(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        $trimmed = trim($path, '/');

        if ($trimmed === '') {
            return null;
        }

        $segments = explode('/', $trimmed);
        $slug = end($segments);

        if (! is_string($slug) || $slug === '') {
            return null;
        }

        return trim($slug);
    }

    private function extractCountryCode(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        if ($segments === []) {
            return null;
        }

        $candidate = strtolower($segments[0]);

        return $candidate !== '' ? $candidate : null;
    }

    private function findProductBySlug(string $slug, ?string $countryCode = null): ?Product
    {
        $candidates = $this->buildSlugCandidates($slug);

        foreach ($candidates as $candidate) {
            if ($product = $this->queryExactSlug($candidate)) {
                return $product;
            }
        }

        foreach ($candidates as $candidate) {
            if ($product = $this->queryNormalizedSlug($candidate)) {
                return $product;
            }
        }

        foreach ($candidates as $candidate) {
            if ($product = $this->queryPartialSlug($candidate)) {
                return $product;
            }
        }

        return $this->searchProductWithMeilisearch($candidates, $countryCode);
    }

    private function queryExactSlug(string $slug): ?Product
    {
        return Product::query()
            ->where('slug', $slug)
            ->first();
    }

    private function queryNormalizedSlug(string $slug): ?Product
    {
        $normalized = $this->normalizeSlugForComparison($slug);

        if ($normalized === '') {
            return null;
        }

        return Product::query()
            ->whereRaw("REPLACE(slug, '-', '') = ?", [$normalized])
            ->orderBy('id')
            ->first();
    }

    private function queryPartialSlug(string $slug): ?Product
    {
        $pattern = '%' . addcslashes($slug, '%_') . '%';

        return Product::query()
            ->where('slug', 'like', $pattern)
            ->orderBy('id')
            ->first();
    }

    private function buildDestinationUrl(string $slug): string
    {
        $cleanSlug = ltrim($slug, '/');

        return rtrim($this->destinationPrefix(), '/') . '/' . $cleanSlug;
    }

    protected function destinationPrefix(): string
    {
        return static::DESTINATION_PREFIX;
    }

    /**
     * @param array<int,string> $terms
     */
    private function searchProductWithMeilisearch(array $terms, ?string $countryCode): ?Product
    {
        if (! $this->canUseMeilisearch()) {
            return null;
        }

        $terms = array_values(array_filter(array_unique($terms)));

        if ($terms === []) {
            return null;
        }

        $countries = $this->prioritizedCountries($countryCode);

        if ($countries === []) {
            return null;
        }

        foreach ($terms as $term) {
            foreach ($countries as $country) {
                $product = $this->queryMeilisearch($term, $country);

                if ($product) {
                    return $product;
                }
            }
        }

        return null;
    }

    private function canUseMeilisearch(): bool
    {
        return class_exists(Catalog::class)
            && \Settings::get('dress.search.enabled', false)
            && \Settings::get('dress.search.driver', 'meilisearch') === 'meilisearch'
            && class_exists(\Laravel\Scout\Builder::class);
    }

    /**
     * @return array<int,string>
     */
    private function prioritizedCountries(?string $preferred): array
    {
        $countries = array_keys(StoreService::countries());

        $countries = array_values(array_filter(array_map(
            static fn ($code) => is_string($code) ? strtolower($code) : null,
            $countries
        )));

        if ($preferred) {
            $preferred = strtolower($preferred);
            array_unshift($countries, $preferred);
        }

        $countries = array_values(array_unique(array_filter($countries)));

        if ($countries === []) {
            $fallback = strtolower((string) \Settings::get('dress.multistore.default_country', ''));

            if ($fallback !== '') {
                $countries[] = $fallback;
            }
        }

        return $countries;
    }

    private function queryMeilisearch(string $term, string $countryCode): ?Product
    {
        $countryCode = strtolower($countryCode);
        $currency = StoreService::countryCurrency($countryCode)
            ?: \Settings::get('dress.multistore.default_currency', 'USD')
            ?: 'USD';

        try {
            $results = StoreService::withContext($countryCode, $currency, function () use ($term, $countryCode) {
                $builder = Catalog::search($term)
                    ->within(Catalog::searchIndexBase() . '_' . $countryCode)
                    ->take(5);

                return $builder->get();
            });
        } catch (\Throwable $e) {
            return null;
        }

        if (! $results) {
            return null;
        }

        foreach ($results as $catalog) {
            $productId = $catalog->product_id ?? null;

            if (! $productId) {
                continue;
            }

            $product = Product::query()->find($productId);

            if ($product) {
                return $product;
            }
        }

        return null;
    }

    /**
     * @return array<int,string>
     */
    private function buildSlugCandidates(string $slug): array
    {
        $candidates = [$slug];
        $decoded = rawurldecode($slug);

        if ($decoded !== $slug) {
            $candidates[] = $decoded;
        }

        return array_values(array_filter(array_unique($candidates), static fn ($candidate) => $candidate !== ''));
    }

    private function normalizeSlugForComparison(string $slug): string
    {
        return str_replace('-', '', $slug);
    }

    private function writeCsv(string $path, array $rows): void
    {
        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Unable to create directory ' . $directory);
        }

        $handle = fopen($path, 'w');

        if ($handle === false) {
            throw new \RuntimeException('Unable to open ' . $path . ' for writing');
        }

        fputcsv($handle, ['source', 'destination', 'permanent']);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
    }

    private function resolvePath(string $path): string
    {
        if ($path === '') {
            return base_path();
        }

        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return base_path($path);
    }

    private function isAbsolutePath(string $path): bool
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return true;
        }

        return (bool) preg_match('/^[A-Za-z]:[\\\\\\/]/', $path);
    }
}
