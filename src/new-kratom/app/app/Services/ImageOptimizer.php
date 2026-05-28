<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Генерация AVIF/WebP/JPEG в нескольких размерах для адаптивного srcset.
 *
 * Вызывается из MediaUploaded job (async), результаты пишутся в
 * media.renditions / product_images.renditions JSON.
 */
class ImageOptimizer
{
    /** Размеры под mobile-first дизайн (от ТЗ vivadzen-design-tz/01) */
    public const SIZES = [480, 768, 1280];
    public const FORMATS = ['avif', 'webp', 'jpg'];

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * @return array<string, array<int, string>> {avif: {480: url, ...}, webp: {...}, jpg: {...}}
     */
    public function generate(string $sourcePath): array
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($sourcePath)) {
            return $this->defaultRenditions($sourcePath);
        }

        $source = $this->manager->read($disk->path($sourcePath));
        $pathInfo = pathinfo($sourcePath);
        $base = $pathInfo['dirname'].'/'.$pathInfo['filename'];

        $renditions = [];
        foreach (self::SIZES as $w) {
            $resized = (clone $source)->scaleDown(width: $w);
            foreach (self::FORMATS as $fmt) {
                $target = "{$base}-{$w}.{$fmt}";
                $encoded = match ($fmt) {
                    'avif' => $resized->toAvif(quality: 60),
                    'webp' => $resized->toWebp(quality: 78),
                    'jpg'  => $resized->toJpeg(quality: 82, progressive: true),
                };
                $disk->put($target, (string) $encoded);
                $renditions[$fmt][$w] = '/storage/'.$target;
            }
        }
        return $renditions;
    }

    /** @return array<string, array<int, string>> */
    public function defaultRenditions(string $path): array
    {
        // Если оптимизатор не отработал, фолбэк — отдаём оригинал во всех размерах.
        $renditions = [];
        foreach (self::FORMATS as $fmt) {
            foreach (self::SIZES as $w) {
                $renditions[$fmt][$w] = $path;
            }
        }
        return $renditions;
    }
}
