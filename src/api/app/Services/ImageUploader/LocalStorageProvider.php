<?php

namespace App\Services\ImageUploader;

use Illuminate\Support\Facades\Storage;

class LocalStorageProvider implements StorageProviderInterface
{
    protected string $disk;
    protected string $urlPrefix;

    public function __construct(string $disk, string $urlPrefix)
    {
        $this->disk = $disk;
        // Убираем завершающий слэш для корректной конкатенации URL
        $this->urlPrefix = rtrim($urlPrefix, '/');
    }

    public function upload(string $content, string $path): string
    {
        // Сохраняем контент файла на указанный диск по заданному пути
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
        // Формируем публичный URL на основе префикса и относительного пути
        return $this->urlPrefix . '/' . ltrim($path, '/');
    }
}
