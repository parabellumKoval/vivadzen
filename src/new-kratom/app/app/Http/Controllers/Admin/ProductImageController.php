<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateImageRenditions;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\MediaStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Полноценная галерея товара: upload/edit/reorder/delete.
 * Хранилище выбирается через config/media.php (local | bunny).
 */
class ProductImageController extends Controller
{
    public function __construct(private readonly MediaStorage $storage) {}

    public function index(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $product->images()->orderBy('position')->get()
                ->map(fn (ProductImage $img) => $this->shape($img)),
        ]);
    }

    /**
     * Принимает multipart: files[] — массив изображений. Создаёт ProductImage
     * для каждого, ставит position в конец списка, ставит renditions-job для public-диска.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $maxKb = (int) config('media.image_max_kb', 10240);
        $mimes = implode(',', config('media.image_mimes', ['jpg', 'jpeg', 'png', 'webp', 'avif']));

        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => "required|file|mimes:{$mimes}|max:{$maxKb}",
        ]);

        $startPos = (int) ($product->images()->max('position') ?? 0) + 1;
        $created = [];

        foreach ($request->file('files') as $i => $file) {
            $stored = $this->storage->store($file, "products/{$product->id}");

            $image = $product->images()->create([
                'disk' => $stored['disk'],
                'path' => $stored['path'],
                'url' => $stored['url'],
                'alt' => null,
                'title' => null,
                'position' => $startPos + $i,
            ]);

            // Адаптивные renditions имеют смысл только для локального public-диска
            // (ImageOptimizer читает $disk->path()). Для bunny оставляем как есть.
            if ($image->disk === 'public') {
                GenerateImageRenditions::dispatch($image->id, 'product_image');
            }

            $created[] = $this->shape($image);
        }

        return response()->json(['data' => $created], 201);
    }

    /**
     * Обновить alt/title (или position при одиночном изменении).
     */
    public function update(Request $request, Product $product, ProductImage $image): JsonResponse
    {
        $this->ensureBelongs($product, $image);

        $data = $request->validate([
            'alt' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:0',
        ]);

        $image->update($data);

        return response()->json(['data' => $this->shape($image->fresh())]);
    }

    public function destroy(Product $product, ProductImage $image): JsonResponse
    {
        $this->ensureBelongs($product, $image);

        $this->storage->delete($image->disk, $image->path);
        // Подчищаем renditions (если оставались на public-диске)
        foreach ((array) $image->renditions as $format) {
            foreach ((array) $format as $url) {
                if (is_string($url) && str_starts_with($url, '/storage/')) {
                    $rel = ltrim(substr($url, strlen('/storage')), '/');
                    $this->storage->delete('public', $rel);
                }
            }
        }

        $image->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Массовая смена порядка: { order: [imageId, imageId, ...] }
     */
    public function reorder(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'order' => 'required|array|min:1',
            'order.*' => 'integer|min:1',
        ]);

        $ids = $data['order'];
        $owned = $product->images()->whereIn('id', $ids)->pluck('id')->all();

        DB::transaction(function () use ($ids, $owned, $product) {
            foreach ($ids as $position => $id) {
                if (! in_array($id, $owned, true)) {
                    continue;
                }
                $product->images()->where('id', $id)->update(['position' => $position + 1]);
            }
        });

        return response()->json([
            'data' => $product->images()->orderBy('position')->get()
                ->map(fn (ProductImage $img) => $this->shape($img)),
        ]);
    }

    private function ensureBelongs(Product $product, ProductImage $image): void
    {
        abort_unless($image->product_id === $product->id, 404);
    }

    /** @return array<string, mixed> */
    private function shape(ProductImage $image): array
    {
        return [
            'id' => $image->id,
            'disk' => $image->disk,
            'path' => $image->path,
            'url' => $image->public_url,
            'alt' => $image->alt,
            'title' => $image->title,
            'position' => $image->position,
            'renditions' => $image->renditions,
        ];
    }
}
