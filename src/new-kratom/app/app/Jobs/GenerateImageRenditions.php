<?php

namespace App\Jobs;

use App\Models\Media;
use App\Services\ImageOptimizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Генерация AVIF/WebP/JPEG в 3 размерах. Тяжёлая операция (~200ms на размер),
 * поэтому уходит в Horizon queue 'images'.
 */
class GenerateImageRenditions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function __construct(public readonly int $mediaId)
    {
    }

    public function handle(ImageOptimizer $optimizer): void
    {
        $media = Media::find($this->mediaId);
        if (! $media) return;

        $renditions = $optimizer->generate($media->path);
        $media->update(['renditions' => $renditions]);
    }
}
