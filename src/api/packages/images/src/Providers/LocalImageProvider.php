<?php

namespace ParabellumKoval\BackpackImages\Providers;

use Illuminate\Support\Facades\Storage;
use ParabellumKoval\BackpackImages\Contracts\ConfigurableImageProvider;
use ParabellumKoval\BackpackImages\Contracts\ImageStorageProvider;

class LocalImageProvider implements ImageStorageProvider, ConfigurableImageProvider
{
    protected string $disk;

    protected string $urlPrefix;

    public function __construct(string $disk, string $urlPrefix)
    {
        $this->disk = $disk;
        $this->urlPrefix = rtrim($urlPrefix, '/');
    }

    public static function fromConfig(array $config): static
    {
        return new static(
            $config['disk'] ?? 'public',
            $config['url_prefix'] ?? ''
        );
    }

    public function upload(string $content, string $path): string
    {
        Storage::disk($this->disk)->put($path, $content);

        return $path;
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    public function getUrl(string $path): string
    {
        $prefix = $this->urlPrefix;

        if ($prefix === '') {
            return ltrim($path, '/');
        }

        return $prefix . '/' . ltrim($path, '/');
    }
}
