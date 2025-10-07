<?php

namespace App\Services\ImageUploader;

interface StorageProviderInterface
{
    public function upload(string $content, string $path): string;
    public function exists(string $path): bool;
    public function delete(string $path): bool;
    public function getUrl(string $path): string;
}