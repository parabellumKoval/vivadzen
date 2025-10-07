<?php

namespace ParabellumKoval\BackpackImages\Support;

use Illuminate\Support\Str;
use ParabellumKoval\BackpackImages\Contracts\ImageStorageProvider;

class FileNameGenerator
{
    public function generate(
        string $originalName,
        string $extension,
        ImageStorageProvider $provider,
        string $folder,
        bool $preserveOriginal,
        bool $generateUnique
    ): string {
        $extension = ltrim($extension, '.');
        $folder = trim($folder, '/');
        $baseName = '';

        if ($preserveOriginal && $originalName !== '') {
            $baseName = pathinfo($originalName, PATHINFO_FILENAME) ?: $this->defaultBaseName();

            if ($generateUnique) {
                $baseName .= '_' . Str::random(8);
            }
        } else {
            $baseName = Str::uuid()->toString();
        }

        $fileName = $baseName . '.' . $extension;
        $pathPrefix = $folder !== '' ? $folder . '/' : '';

        if (!$generateUnique) {
            $fileName = $this->avoidCollision($provider, $pathPrefix, $baseName, $extension);
        }

        return $pathPrefix . $fileName;
    }

    protected function avoidCollision(ImageStorageProvider $provider, string $pathPrefix, string $baseName, string $extension): string
    {
        $fileName = $baseName . '.' . $extension;
        $counter = 2;

        while ($provider->exists($pathPrefix . $fileName)) {
            $fileName = sprintf('%s_(%d).%s', $baseName, $counter, $extension);
            $counter++;
        }

        return $fileName;
    }

    protected function defaultBaseName(): string
    {
        return 'image_' . date('Ymd_His');
    }
}
