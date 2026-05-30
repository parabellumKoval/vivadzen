<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\ProductReview;
use App\Models\ProductReviewImage;
use App\Support\Catalog;
use App\Support\Locale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Публичные endpoint-ы для отзывов / вопросов покупателей.
 *
 * Новые записи приходят со статусом `pending` — без авто-публикации.
 * Менеджер модерирует через админ-API. На list-endpoint отдаём
 * только approved + published_at <= now().
 */
class ReviewController extends Controller
{
    public const MAX_PHOTOS = 3;
    public const MAX_PHOTO_SIZE_KB = 5120; // 5 MB

    public function listReviews(string $slug, Request $request): JsonResponse
    {
        $product = $this->resolveProduct($slug);

        $query = $product->reviews()->with('images')->public();
        $query = $this->applyReviewFilters($query, $request);
        $query = $this->applyReviewSort($query, $request->query('sort', 'newest'));

        $perPage = (int) $request->integer('per_page', 6);
        $perPage = max(1, min(50, $perPage));

        $reviews = $query->paginate($perPage);

        return response()->json([
            'data' => $reviews->getCollection()->map(fn (ProductReview $r) => $this->shapeReview($r))->all(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'total'        => $reviews->total(),
            ],
        ]);
    }

    public function storeReview(string $slug, Request $request): JsonResponse
    {
        $product = $this->resolveProduct($slug);

        $data = $request->validate([
            'author_name'  => 'required|string|max:120',
            'author_email' => 'nullable|email|max:190',
            'rating'       => 'required|integer|min:1|max:5',
            'body'         => 'required|string|min:10|max:4000',
            'photos'       => 'nullable|array|max:' . self::MAX_PHOTOS,
            'photos.*'     => 'image|mimes:jpg,jpeg,png,webp,heic|max:' . self::MAX_PHOTO_SIZE_KB,
        ]);

        $this->createPendingReview($product, $data, $request);

        return response()->json([
            'ok'      => true,
            'message' => 'Děkujeme za recenzi! Po krátké kontrole se objeví na stránce.',
        ], 201);
    }

    /**
     * Страница «Recenze Vivadzen» — все отзывы о всех товарах.
     */
    public function page(): View
    {
        $products = collect(Catalog::products())
            ->map(fn (array $p) => [
                'slug'  => $p['slug'],
                'name'  => $p['name'],
                'url'   => Locale::url('/kratom/' . $p['slug']),
                'image' => $p['image'] ?? null,
                'price' => $p['price25'] ?? $p['price50'] ?? null,
            ])
            ->sortBy('name')
            ->values()
            ->all();

        return view('pages.reviews', [
            'products' => $products,
        ]);
    }

    /**
     * Глобальный список отзывов (все товары) с фильтрами/сортировкой.
     */
    public function listAllReviews(Request $request): JsonResponse
    {
        $query = ProductReview::query()->with(['images', 'product:id,slug'])->public();

        if ($slug = $request->query('product')) {
            $query->whereHas('product', fn ($q) => $q->where('slug', $slug));
        }

        $query = $this->applyReviewFilters($query, $request);
        $query = $this->applyReviewSort($query, $request->query('sort', 'newest'));

        $perPage = (int) $request->integer('per_page', 9);
        $perPage = max(1, min(50, $perPage));

        $reviews = $query->paginate($perPage);

        $productLookup = collect(Catalog::products())->keyBy('slug');

        return response()->json([
            'data' => $reviews->getCollection()
                ->map(fn (ProductReview $r) => $this->shapeReview($r) + [
                    'product' => $this->productContext($productLookup->get($r->product?->slug)),
                ])
                ->all(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'total'        => $reviews->total(),
            ],
        ]);
    }

    /**
     * Отправка отзыва со страницы «Recenze» — товар выбирается из списка.
     */
    public function storeGlobalReview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product'      => 'required|string',
            'author_name'  => 'required|string|max:120',
            'author_email' => 'nullable|email|max:190',
            'rating'       => 'required|integer|min:1|max:5',
            'body'         => 'required|string|min:10|max:4000',
            'photos'       => 'nullable|array|max:' . self::MAX_PHOTOS,
            'photos.*'     => 'image|mimes:jpg,jpeg,png,webp,heic|max:' . self::MAX_PHOTO_SIZE_KB,
        ]);

        $product = $this->resolveProduct($data['product']);

        $this->createPendingReview($product, $data, $request);

        return response()->json([
            'ok'      => true,
            'message' => 'Děkujeme za recenzi! Po krátké kontrole se objeví na stránce.',
        ], 201);
    }

    public function listQuestions(string $slug, Request $request): JsonResponse
    {
        $product = $this->resolveProduct($slug);

        $perPage = (int) $request->integer('per_page', 5);
        $perPage = max(1, min(50, $perPage));

        $questions = $product->questions()
            ->public()
            ->orderByDesc('published_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $questions->getCollection()->map(fn (ProductQuestion $q) => $this->shapeQuestion($q))->all(),
            'meta' => [
                'current_page' => $questions->currentPage(),
                'last_page'    => $questions->lastPage(),
                'total'        => $questions->total(),
            ],
        ]);
    }

    public function storeQuestion(string $slug, Request $request): JsonResponse
    {
        $product = $this->resolveProduct($slug);

        $data = $request->validate([
            'author_name'  => 'required|string|max:120',
            'author_email' => 'nullable|email|max:190',
            'question'     => 'required|string|min:5|max:2000',
        ]);

        ProductQuestion::create([
            'product_id'   => $product->id,
            'author_name'  => $data['author_name'],
            'author_email' => $data['author_email'] ?? null,
            'question'     => $data['question'],
            'status'       => 'pending',
        ]);

        return response()->json([
            'ok'      => true,
            'message' => 'Děkujeme za dotaz. Odpovíme do 24 hodin.',
        ], 201);
    }

    public function helpfulReview(ProductReview $review): JsonResponse
    {
        $review->increment('helpful_count');
        return response()->json(['ok' => true, 'helpful' => $review->helpful_count]);
    }

    public function helpfulQuestion(ProductQuestion $question): JsonResponse
    {
        $question->increment('helpful_count');
        return response()->json(['ok' => true, 'helpful' => $question->helpful_count]);
    }

    // ─── helpers ────────────────────────────────────────────────────

    private function resolveProduct(string $slug): Product
    {
        return Product::where('slug', $slug)->firstOrFail();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createPendingReview(Product $product, array $data, Request $request): ProductReview
    {
        $review = ProductReview::create([
            'product_id'   => $product->id,
            'user_id'      => $request->user()?->id,
            'author_name'  => $data['author_name'],
            'author_email' => $data['author_email'] ?? null,
            'rating'       => $data['rating'],
            'body'         => $data['body'],
            'status'       => 'pending',
            // published_at — менеджер выставит при подтверждении
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $i => $file) {
                if ($i >= self::MAX_PHOTOS) break;
                $path = $file->store('reviews/' . $product->id, 'public');
                ProductReviewImage::create([
                    'product_review_id' => $review->id,
                    'path'              => '/storage/' . $path,
                    'position'          => $i,
                ]);
            }
        }

        return $review;
    }

    /**
     * @param array<string, mixed>|null $product
     * @return array<string, mixed>|null
     */
    private function productContext(?array $product): ?array
    {
        if (! $product) {
            return null;
        }

        return [
            'slug'  => $product['slug'],
            'name'  => $product['name'],
            'url'   => Locale::url('/kratom/' . $product['slug']),
            'image' => $product['image'] ?? null,
            'price' => $product['price25'] ?? $product['price50'] ?? null,
        ];
    }

    private function applyReviewFilters($query, Request $request)
    {
        if ($rating = $request->query('rating')) {
            $query->where('rating', (int) $rating);
        }
        if ($request->boolean('verified')) {
            $query->where('verified_purchase', true);
        }
        if ($request->boolean('with_photos')) {
            $query->whereHas('images');
        }
        return $query;
    }

    private function applyReviewSort($query, ?string $sort)
    {
        return match ($sort) {
            'highest' => $query->orderByDesc('rating')->orderByDesc('published_at'),
            'lowest'  => $query->orderBy('rating')->orderByDesc('published_at'),
            'photos'  => $query->withCount('images')->orderByDesc('images_count')->orderByDesc('published_at'),
            default   => $query->orderByDesc('published_at'),
        };
    }

    private function shapeReview(ProductReview $r): array
    {
        return [
            'id'             => $r->id,
            'author'         => $r->author_name,
            'rating'         => (int) $r->rating,
            'body'           => $r->body,
            'verified'       => (bool) $r->verified_purchase,
            'helpful'        => (int) $r->helpful_count,
            'published_at'   => $r->published_at?->toIso8601String(),
            'date'           => $r->published_at?->locale('cs')->diffForHumans(),
            'images'         => $r->images->map(fn ($img) => $img->path)->values()->all(),
        ];
    }

    private function shapeQuestion(ProductQuestion $q): array
    {
        return [
            'id'           => $q->id,
            'author'       => $q->author_name,
            'question'     => $q->question,
            'answer'       => $q->answer,
            'answered_by'  => $q->answered_by,
            'answered_at'  => $q->answered_at?->toIso8601String(),
            'helpful'      => (int) $q->helpful_count,
            'published_at' => $q->published_at?->toIso8601String(),
            'date'         => $q->published_at?->locale('cs')->isoFormat('D. MM. YYYY'),
        ];
    }
}
