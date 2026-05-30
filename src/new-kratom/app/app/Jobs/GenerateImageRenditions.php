<?php

namespace App\Jobs;

use App\Models\Media;
use App\Models\ProductImage;
use App\Services\ImageOptimizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Генерация AVIF/WebP/JPEG в 3 размерах. Тяжёлая операция (~200ms на размер),
 * поэтому уходит в Horizon queue 'images'.
 *
 * Поддерживает два типа целей:
 *   - 'media'         (App\Models\Media)
 *   - 'product_image' (App\Models\ProductImage)
 */
class GenerateImageRenditions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function __construct(
        public readonly int $modelId,
        public readonly string $modelType = 'media',
    ) {
    }

    public function handle(ImageOptimizer $optimizer): void
    {
        $model = match ($this->modelType) {
            'product_image' => ProductImage::find($this->modelId),
            default => Media::find($this->modelId),
        };

        if (! $model) {
            return;
        }

        // Адаптивные размеры пишем только для public-диска: ImageOptimizer читает Storage::disk('public')
        $disk = $model->disk ?? 'public';
        if ($disk !== 'public') {
            return;
        }

        $renditions = $optimizer->generate($model->path);
        $model->update(['renditions' => $renditions]);
    }
}
