<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductReviewImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Админ-CRUD для отзывов: модерация + ручное создание (менеджер
 * пишет отзыв задним числом или планирует на будущее через published_at).
 */
class ReviewController extends Controller
{
    public const MAX_PHOTOS = 3;
    public const MAX_PHOTO_SIZE_KB = 5120;

    public function index(Request $request): JsonResponse
    {
        $query = ProductReview::query()->with(['product:id,slug,name', 'images']);

        if ($status = $request->query('status')) {
            if ($status === 'scheduled') {
                $query->where('status', 'approved')->where('published_at', '>', now());
            } else {
                $query->where('status', $status);
            }
        }

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('author_name', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($productId = $request->integer('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($rating = $request->integer('rating')) {
            $query->where('rating', $rating);
        }

        $reviews = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 25));
        $reviews->getCollection()->transform(fn (ProductReview $r) => $this->shape($r));

        return response()->json(['data' => $reviews]);
    }

    public function show(ProductReview $review): JsonResponse
    {
        return response()->json(['data' => $this->shape($review->load(['product:id,slug,name', 'images']))]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateReview($request);
        $product = Product::findOrFail($data['product_id']);

        $review = ProductReview::create($data + [
            'created_by_admin_id' => $request->user()?->id,
        ]);

        $this->syncPhotos($request, $review);

        return response()->json([
            'data' => $this->shape($review->fresh(['product:id,slug,name', 'images'])),
        ], 201);
    }

    public function update(Request $request, ProductReview $review): JsonResponse
    {
        $data = $this->validateReview($request, $review);
        $review->update($data);

        $this->syncPhotos($request, $review);

        return response()->json([
            'data' => $this->shape($review->fresh(['product:id,slug,name', 'images'])),
        ]);
    }

    public function destroy(ProductReview $review): JsonResponse
    {
        $review->delete();
        return response()->json(['ok' => true]);
    }

    public function approve(Request $request, ProductReview $review): JsonResponse
    {
        $publishedAt = $request->input('published_at')
            ? \Illuminate\Support\Carbon::parse($request->input('published_at'))
            : ($review->published_at ?? now());

        $review->update([
            'status'       => 'approved',
            'published_at' => $publishedAt,
        ]);
        return response()->json(['data' => $this->shape($review->fresh(['product:id,slug,name', 'images']))]);
    }

    public function reject(ProductReview $review): JsonResponse
    {
        $review->update(['status' => 'rejected', 'published_at' => null]);
        return response()->json(['data' => $this->shape($review->fresh(['product:id,slug,name', 'images']))]);
    }

    public function deletePhoto(ProductReview $review, ProductReviewImage $image): JsonResponse
    {
        abort_unless($image->product_review_id === $review->id, 404);
        $image->delete();
        return response()->json(['ok' => true]);
    }

    // ─── helpers ───────────────────────────────────────────────────

    private function validateReview(Request $request, ?ProductReview $existing = null): array
    {
        $rules = [
            'product_id'        => ($existing ? 'sometimes|' : '') . 'required|integer|exists:products,id',
            'author_name'       => ($existing ? 'sometimes|' : '') . 'required|string|max:120',
            'author_email'      => 'nullable|email|max:190',
            'rating'            => ($existing ? 'sometimes|' : '') . 'required|integer|min:1|max:5',
            'body'              => ($existing ? 'sometimes|' : '') . 'required|string|min:5|max:4000',
            'verified_purchase' => 'boolean',
            'helpful_count'     => 'nullable|integer|min:0',
            'status'            => 'nullable|in:pending,approved,rejected',
            'published_at'      => 'nullable|date',
            'photos'            => 'nullable|array|max:' . self::MAX_PHOTOS,
            'photos.*'          => 'image|mimes:jpg,jpeg,png,webp,heic|max:' . self::MAX_PHOTO_SIZE_KB,
        ];

        $validated = $request->validate($rules);

        // photos handled separately
        unset($validated['photos']);

        return $validated;
    }

    private function syncPhotos(Request $request, ProductReview $review): void
    {
        if (! $request->hasFile('photos')) {
            return;
        }

        $existing = $review->images()->count();
        foreach ($request->file('photos') as $i => $file) {
            if ($existing + $i >= self::MAX_PHOTOS) break;
            $path = $file->store('reviews/' . $review->product_id, 'public');
            ProductReviewImage::create([
                'product_review_id' => $review->id,
                'path'              => '/storage/' . $path,
                'position'          => $existing + $i,
            ]);
        }
    }

    private function shape(ProductReview $review): array
    {
        $payload = $review->toArray();
        $payload['images'] = $review->relationLoaded('images')
            ? $review->images->map(fn ($img) => ['id' => $img->id, 'path' => $img->path, 'position' => $img->position])->all()
            : [];
        $payload['product'] = $review->product ? [
            'id'   => $review->product->id,
            'slug' => $review->product->slug,
            'name' => is_array($review->product->name) ? ($review->product->name['cs'] ?? null) : $review->product->name,
        ] : null;
        return $payload;
    }
}
