<?php

namespace ParabellumKoval\BackpackImages\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use ParabellumKoval\BackpackImages\DTO\StoredImage;
use ParabellumKoval\BackpackImages\Services\ImageUploader;
use ParabellumKoval\BackpackImages\Support\ImageUploadOptions;
use ParabellumKoval\BackpackImages\Casts\ImageCollectionCast;

trait HasImages
{
    /**
     * Register casts for every configured image collection.
     */
    public function initializeHasImages(): void
    {
        foreach (array_keys(static::resolvedImageCollections()) as $attribute) {
            $this->casts[$attribute] = ImageCollectionCast::class . ':' . $attribute;
        }
    }

    /**
     * Default attribute name used when no collection specified.
     */
    public static function imageAttributeName(): string
    {
        return 'images';
    }

    /**
     * Base image collection definitions. Models can override {@see imageCollections}
     * and extend this list.
     */
    protected static function defaultImageCollections(): array
    {
        return [
            static::imageAttributeName() => [],
        ];
    }

    /**
     * Image collection configuration map.
     *
     * @return array<string, array>
     */
    public static function imageCollections(): array
    {
        return static::defaultImageCollections();
    }

    /**
     * Resolved image collections with defaults applied.
     *
     * @return array<string, array>
     */
    protected static function resolvedImageCollections(): array
    {
        $collections = static::imageCollections();
        $resolved = [];

        foreach ($collections as $key => $config) {
            if (is_int($key)) {
                if (!is_string($config) || $config === '') {
                    continue;
                }

                $attribute = $config;
                $config = [];
            } else {
                $attribute = (string) $key;
            }

            if ($attribute === '') {
                continue;
            }

            $resolved[$attribute] = static::prepareImageCollectionConfig($attribute, (array) $config);
        }

        return $resolved;
    }

    protected static function prepareImageCollectionConfig(string $attribute, array $config): array
    {
        $label = $config['label'] ?? __('images::base.images');
        $tab = $config['tab'] ?? __('images::base.images');
        $newItemLabel = $config['new_item_label'] ?? __('images::base.add_image');
        $columnLimit = (int) ($config['column_limit'] ?? 1);

        $provider = $config['provider'] ?? config('backpack-images.default_provider', 'local');
        $folder = $config['folder'] ?? config('backpack-images.default_folder', 'images');
        $prefix = $config['prefix'] ?? static::resolveUrlPrefix($provider);

        $fieldDefinition = static::buildDefaultFieldDefinition($attribute, $label, $tab, $newItemLabel, $prefix);
        if (isset($config['field'])) {
            $fieldDefinition = array_replace_recursive($fieldDefinition, $config['field']);
        }
        $fieldDefinition['name'] = $attribute;
        $fieldDefinition['label'] ??= $label;
        if (!array_key_exists('tab', $fieldDefinition)) {
            $fieldDefinition['tab'] = $tab;
        }

        $columnDefinition = static::buildDefaultColumnDefinition($attribute, $label, $prefix, $columnLimit);
        if (isset($config['column'])) {
            $columnDefinition = array_replace_recursive($columnDefinition, $config['column']);
        }
        $columnDefinition['name'] = $attribute;
        $columnDefinition['label'] ??= $label;

        $options = static::buildImageUploadOptions($config, $provider, $folder);

        return [
            'attribute' => $attribute,
            'label' => $label,
            'tab' => $tab,
            'new_item_label' => $newItemLabel,
            'prefix' => $prefix,
            'column_limit' => $columnLimit,
            'options' => $options,
            'field' => $fieldDefinition,
            'column' => $columnDefinition,
        ];
    }

    protected static function buildDefaultFieldDefinition(string $attribute, string $label, string $tab, string $newItemLabel, string $prefix): array
    {
        return [
            'name' => $attribute,
            'label' => $label,
            'type' => 'repeatable',
            'fields' => [
                [
                    'name' => 'src',
                    'label' => __('images::base.image'),
                    'type' => 'image',
                    'crop' => false,
                    'prefix' => $prefix,
                ],
                [
                    'name' => 'alt',
                    'label' => 'alt',
                ],
                [
                    'name' => 'title',
                    'label' => 'title',
                    'type' => 'text',
                ],
                [
                    'name' => 'size',
                    'type' => 'radio',
                    'label' => __('images::base.size'),
                    'options' => [
                        'cover' => 'Cover',
                        'contain' => 'Contain',
                    ],
                    'inline' => true,
                ],
            ],
            'new_item_label' => $newItemLabel,
            'init_rows' => 1,
            'tab' => $tab,
            'default' => [],
        ];
    }

    protected static function buildDefaultColumnDefinition(string $attribute, string $label, string $prefix, int $limit): array
    {
        return [
            'name' => $attribute,
            'label' => $label,
            'type' => 'closure',
            'escaped' => false,
            'function' => function ($entry) use ($attribute, $prefix, $limit) {
                if (!method_exists($entry, 'getImageCollectionPaths')) {
                    return '';
                }

                $paths = $entry->getImageCollectionPaths($attribute, $limit);

                return collect($paths)
                    ->map(function ($path) use ($entry, $attribute, $prefix) {
                        $url = $entry->formatImageUrlForAttribute($attribute, $path, $prefix);

                        if (!$url) {
                            return '';
                        }

                        return '<img src="' . e($url) . '" style="max-height:60px;margin-right:4px;border-radius:4px;" />';
                    })
                    ->implode('');
            },
        ];
    }

    protected static function buildImageUploadOptions(array $config, string $provider, string $folder): ImageUploadOptions
    {
        $options = $config['upload_options'] ?? null;

        if ($options instanceof ImageUploadOptions) {
            return $options;
        }

        $options = is_array($options) ? $options : [];

        return new ImageUploadOptions(
            provider: $options['provider'] ?? $provider,
            folder: $options['folder'] ?? $folder,
            preserveOriginalName: $options['preserve_original_name'] ?? null,
            generateUniqueName: $options['generate_unique_name'] ?? null,
            logChannel: $options['log_channel'] ?? null
        );
    }

    protected static function resolveUrlPrefix(string $provider): string
    {
        $prefix = config("backpack-images.providers.$provider.url_prefix");

        if (is_string($prefix) && $prefix !== '') {
            return $prefix;
        }

        return config('backpack-images.default_url_prefix', '/');
    }

    protected static function getImageCollectionConfig(string $attribute): array
    {
        $collections = static::resolvedImageCollections();

        if (!array_key_exists($attribute, $collections)) {
            throw new InvalidArgumentException(sprintf(
                'Image collection [%s] is not defined for %s.',
                $attribute,
                static::class
            ));
        }

        return $collections[$attribute];
    }

    public static function imageFieldLabel(?string $attribute = null): string
    {
        $attribute ??= static::imageAttributeName();

        return static::getImageCollectionConfig($attribute)['label'];
    }

    public static function imageFieldTabLabel(?string $attribute = null): string
    {
        $attribute ??= static::imageAttributeName();

        return static::getImageCollectionConfig($attribute)['tab'];
    }

    public static function imageFieldNewItemLabel(?string $attribute = null): string
    {
        $attribute ??= static::imageAttributeName();

        return static::getImageCollectionConfig($attribute)['new_item_label'];
    }

    public static function imageProviderName(?string $attribute = null): string
    {
        $attribute ??= static::imageAttributeName();
        /** @var ImageUploadOptions $options */
        $options = static::getImageCollectionConfig($attribute)['options'];

        return $options->provider ?? config('backpack-images.default_provider', 'local');
    }

    public static function imageStorageFolder(?string $attribute = null): string
    {
        $attribute ??= static::imageAttributeName();
        /** @var ImageUploadOptions $options */
        $options = static::getImageCollectionConfig($attribute)['options'];

        return $options->folder ?? config('backpack-images.default_folder', 'images');
    }

    public static function imageFieldPrefix(?string $attribute = null): string
    {
        $attribute ??= static::imageAttributeName();

        return static::getImageCollectionConfig($attribute)['prefix'];
    }

    public static function imageUploadOptions(?string $attribute = null): ImageUploadOptions
    {
        $attribute ??= static::imageAttributeName();

        return clone static::getImageCollectionConfig($attribute)['options'];
    }

    public static function imageColumnPreviewLimit(?string $attribute = null): int
    {
        $attribute ??= static::imageAttributeName();

        return static::getImageCollectionConfig($attribute)['column_limit'];
    }

    public static function imageFieldDefinition(?string $attribute = null): array
    {
        $attribute ??= static::imageAttributeName();

        return static::getImageCollectionConfig($attribute)['field'];
    }

    public static function imageColumnDefinition(?string $attribute = null): array
    {
        $attribute ??= static::imageAttributeName();

        return static::getImageCollectionConfig($attribute)['column'];
    }

    public static function defaultImagesFieldDefinition(): array
    {
        return static::imageFieldDefinition();
    }

    public static function defaultImagesColumnDefinition(): array
    {
        return static::imageColumnDefinition();
    }

    /**
     * @return string[]
     */
    public static function imageAttributeNames(): array
    {
        return array_keys(static::resolvedImageCollections());
    }

    public function imageUploader(): ImageUploader
    {
        return app(ImageUploader::class);
    }

    public function uploadImageFromUrl(string $url, ?ImageUploadOptions $options = null, ?string $attribute = null): StoredImage
    {
        $attribute ??= static::imageAttributeName();
        $defaultOptions = static::imageUploadOptions($attribute);
        $options = $options ? $options->merge($defaultOptions) : $defaultOptions;

        return $this->imageUploader()->upload($url, $options);
    }

    protected function isImageAttribute(string $attribute): bool
    {
        return in_array($attribute, static::imageAttributeNames(), true);
    }

    public function normalizeImagesForAttribute(string $attribute, $value): array
    {
        return $this->normalizeImages($value);
    }

    protected function processImagesBeforeSaving(string $attribute, array $images): array
    {
        $config = static::getImageCollectionConfig($attribute);
        /** @var ImageUploadOptions $options */
        $options = clone $config['options'];
        $prefix = $config['prefix'];

        return collect($images)
            ->map(fn (array $image) => $this->processSingleImage($image, $options, $prefix))
            ->filter()
            ->values()
            ->all();
    }

    protected function normalizeImages($value): array
    {
        if (is_array($value)) {
            return array_values(array_map(fn ($item) => is_array($item) ? $item : (array) $item, $value));
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeImages($decoded);
            }
        }

        return [];
    }

    protected function processSingleImage(array $image, ImageUploadOptions $options, string $prefix): ?array
    {
        if (!array_key_exists('src', $image)) {
            return $image;
        }

        $src = trim((string) $image['src']);

        if ($src === '') {
            $image['src'] = '';

            return $image;
        }

        if ($this->isBase64Image($src)) {
            $stored = $this->imageUploader()->uploadFromBase64($src, $options);
            $image['src'] = $stored->path;

            return $image;
        }

        $image['src'] = $this->normalizeStoredImagePath($src, $prefix);

        return $image;
    }

    protected function isBase64Image(string $value): bool
    {
        return str_starts_with($value, 'data:') && str_contains($value, ';base64,');
    }

    protected function normalizeStoredImagePath(string $path, string $prefix): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $path)) {
            if ($prefix !== '') {
                $normalizedPrefix = rtrim($prefix, '/');

                if ($normalizedPrefix !== '' && str_starts_with($path, $normalizedPrefix)) {
                    $path = substr($path, strlen($normalizedPrefix));
                }
            }

            return ltrim($path, '/');
        }

        if ($prefix !== '') {
            $normalizedPrefix = rtrim($prefix, '/');

            if ($normalizedPrefix !== '' && str_starts_with($path, $normalizedPrefix)) {
                $path = substr($path, strlen($normalizedPrefix));
            }
        }

        return ltrim($path, '/');
    }

    public function getImagesCollection(?string $attribute = null): Collection
    {
        $attribute ??= static::imageAttributeName();
        $value = $this->getAttributeValue($attribute) ?? [];

        return collect($value)
            ->map(fn ($item) => is_array($item) ? $item : (array) $item)
            ->filter();
    }

    public function getImageCollection(string $attribute): Collection
    {
        return $this->getImagesCollection($attribute);
    }

    public function getAllImages(?string $attribute = null): array
    {
        return $this->getImagesCollection($attribute)->values()->all();
    }

    public function getFirstImage(?string $attribute = null): ?array
    {
        return $this->getImagesCollection($attribute)->first();
    }

    public function getImagesLimited(int $limit, ?string $attribute = null): array
    {
        return $this->getImagesCollection($attribute)->take($limit)->values()->all();
    }

    public function getImagePaths(?int $limit = null, ?string $attribute = null): array
    {
        $attribute ??= static::imageAttributeName();

        return $this->pluckImageAttribute($attribute, 'src', $limit);
    }

    public function getImageUrls(?int $limit = null, ?string $attribute = null): array
    {
        $attribute ??= static::imageAttributeName();
        $config = static::getImageCollectionConfig($attribute);
        $prefix = $config['prefix'];

        return array_map(
            fn ($path) => $this->formatImageUrlForAttribute($attribute, $path, $prefix),
            $this->getImagePaths($limit, $attribute)
        );
    }

    public function getImageSources(?int $limit = null, ?string $attribute = null): array
    {
        return $this->getImagePaths($limit, $attribute);
    }

    public function getFirstImageForApi(?string $attribute = null): ?array
    {
        return $this->getImageSourcesForApi(1, true, $attribute)[0] ?? null;
    }

    public function getImageSourcesForApi(?int $limit = null, bool $withPrefix = true, ?string $attribute = null): array
    {
        $attribute ??= static::imageAttributeName();
        $config = static::getImageCollectionConfig($attribute);
        $prefix = $withPrefix ? $config['prefix'] : '';

        return $this->getImagesCollection($attribute)
            ->when($limit !== null, fn ($collection) => $collection->take($limit))
            ->map(function ($image) use ($prefix) {
                $data = Arr::only($image, ['src', 'alt', 'title', 'size']);

                if ($prefix && isset($data['src'])) {
                    $data['src'] = static::formatImageUrl($data['src'], $prefix);
                }

                return $data;
            })
            ->values()
            ->all();
    }

    public function getImageCollectionPaths(string $attribute, ?int $limit = null): array
    {
        return $this->pluckImageAttribute($attribute, 'src', $limit);
    }

    protected function pluckImageAttribute(string $attribute, string $key, ?int $limit = null): array
    {
        $collection = $this->getImagesCollection($attribute);

        if ($limit !== null) {
            $collection = $collection->take($limit);
        }

        return $collection
            ->map(fn ($image) => Arr::get($image, $key))
            ->filter()
            ->values()
            ->all();
    }

    public function formatImageUrlForAttribute(string $attribute, ?string $path, ?string $prefix = null): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $prefix ??= static::getImageCollectionConfig($attribute)['prefix'];

        return static::formatImageUrl($path, $prefix);
    }

    public function prepareImageCollectionValueForStorage(string $attribute, $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = $this->normalizeImagesForAttribute($attribute, $value);
        $processed = $this->processImagesBeforeSaving($attribute, $normalized);

        if (empty($processed)) {
            return null;
        }

        return json_encode($processed, JSON_UNESCAPED_UNICODE);
    }

    protected function prefixUrl(string $path, string $prefix): string
    {
        return static::formatImageUrl($path, $prefix);
    }

    protected static function formatImageUrl(string $path, string $prefix): string
    {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');
        $prefix = rtrim($prefix, '/');

        if ($prefix === '') {
            return $path;
        }

        if ($path === '') {
            return $prefix;
        }

        return $prefix . '/' . $path;
    }

    protected function decodeJsonValue($value)
    {
        if (is_string($value)) {
            $value = trim($value);

            if ($value === '') {
                return null;
            }

            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    protected function makeUrlFromPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url($path);
    }
}
