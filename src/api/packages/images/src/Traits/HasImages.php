<?php

namespace ParabellumKoval\BackpackImages\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use ParabellumKoval\BackpackImages\DTO\StoredImage;
use ParabellumKoval\BackpackImages\Services\ImageUploader;
use ParabellumKoval\BackpackImages\Support\ImageUploadOptions;

trait HasImages
{
    public function initializeHasImages(): void
    {
        $attribute = static::imageAttributeName();
        $this->casts[$attribute] = 'array';
    }

    public static function imageAttributeName(): string
    {
        return 'images';
    }

    public static function imageFieldLabel(): string
    {
        return __('Images');
    }

    public static function imageProviderName(): string
    {
        return config('backpack-images.default_provider', 'local');
    }

    public static function imageStorageFolder(): string
    {
        return config('backpack-images.default_folder', 'images');
    }

    public static function imageFieldPrefix(): string
    {
        $provider = static::imageProviderName();
        $prefix = config("backpack-images.providers.$provider.url_prefix");

        if (is_string($prefix) && $prefix !== '') {
            return $prefix;
        }

        return config('backpack-images.default_url_prefix', '/');
    }

    public static function imageUploadOptions(): ImageUploadOptions
    {
        return new ImageUploadOptions(
            provider: static::imageProviderName(),
            folder: static::imageStorageFolder()
        );
    }

    public static function imageColumnPreviewLimit(): int
    {
        return 3;
    }

    public static function imageFieldNewItemLabel(): string
    {
        return __('Add image');
    }

    public static function defaultImagesFieldDefinition(): array
    {
        return [
            'name' => static::imageAttributeName(),
            'label' => static::imageFieldLabel(),
            'type' => 'repeatable',
            'fields' => [
                [
                    'name' => 'src',
                    'label' => __('Image'),
                    'type' => 'image',
                    'crop' => false,
                    'prefix' => static::imageFieldPrefix(),
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
                    'label' => __('Size'),
                    'options' => [
                        'cover' => 'Cover',
                        'contain' => 'Contain',
                    ],
                    'inline' => true,
                ],
            ],
            'new_item_label' => static::imageFieldNewItemLabel(),
            'init_rows' => 1,
            'default' => [],
        ];
    }

    public static function defaultImagesColumnDefinition(): array
    {
        $attribute = static::imageAttributeName();
        $label = static::imageFieldLabel();
        $prefix = static::imageFieldPrefix();
        $limit = static::imageColumnPreviewLimit();

        return [
            'name' => $attribute,
            'label' => $label,
            'type' => 'closure',
            'function' => function ($entry) use ($prefix, $limit) {
                if (!method_exists($entry, 'getImagePaths')) {
                    return '';
                }

                $paths = $entry->getImagePaths($limit);

                return collect($paths)
                    ->map(function ($path) use ($prefix) {
                        $url = static::formatImageUrl($path, $prefix);

                        return '<img src="' . e($url) . '" style="max-height:60px;margin-right:4px;border-radius:4px;" />';
                    })
                    ->implode('');
            },
            'escaped' => false,
        ];
    }

    public function imageUploader(): ImageUploader
    {
        return app(ImageUploader::class);
    }

    public function uploadImageFromUrl(string $url, ?ImageUploadOptions $options = null): StoredImage
    {
        $options = $options ? $options->merge(static::imageUploadOptions()) : static::imageUploadOptions();

        return $this->imageUploader()->upload($url, $options);
    }

    public function getImagesAttribute($value): array
    {
        return $this->normalizeImages($value);
    }

    public function setImagesAttribute($value): void
    {
        $attribute = static::imageAttributeName();
        $normalized = $this->normalizeImages($value);
        $this->attributes[$attribute] = json_encode($normalized, JSON_UNESCAPED_UNICODE);
    }

    public function getImagesCollection(): Collection
    {
        $value = $this->{static::imageAttributeName()} ?? [];

        return collect($value)
            ->map(fn ($item) => is_array($item) ? $item : (array) $item)
            ->filter();
    }

    public function getAllImages(): array
    {
        return $this->getImagesCollection()->values()->all();
    }

    public function getFirstImage(): ?array
    {
        return $this->getImagesCollection()->first();
    }

    public function getImagesLimited(int $limit): array
    {
        return $this->getImagesCollection()->take($limit)->values()->all();
    }

    public function getImagePaths(?int $limit = null): array
    {
        return $this->pluckImageAttribute('src', $limit);
    }

    public function getImageUrls(?int $limit = null): array
    {
        $prefix = static::imageFieldPrefix();

        return array_map(fn ($path) => static::formatImageUrl($path, $prefix), $this->getImagePaths($limit));
    }

    public function getFirstImagePath(): ?string
    {
        return Arr::first($this->getImagePaths(1));
    }

    public function getFirstImageUrl(): ?string
    {
        return Arr::first($this->getImageUrls(1));
    }

    public function getImageSources(?int $limit = null): array
    {
        return $this->getImagePaths($limit);
    }

    public function getImageSourcesForApi(?int $limit = null): array
    {
        return $this->getImagesCollection()->when($limit !== null, fn ($collection) => $collection->take($limit))
            ->map(fn ($image) => Arr::only($image, ['src', 'alt', 'title', 'size']))
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

    protected function pluckImageAttribute(string $key, ?int $limit = null): array
    {
        $collection = $this->getImagesCollection();

        if ($limit !== null) {
            $collection = $collection->take($limit);
        }

        return $collection
            ->map(fn ($image) => Arr::get($image, $key))
            ->filter()
            ->values()
            ->all();
    }

    protected function prefixUrl(string $path, string $prefix): string
    {
        return static::formatImageUrl($path, $prefix);
    }

    protected static function formatImageUrl(string $path, string $prefix): string
    {
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
}
