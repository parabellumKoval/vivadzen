<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Taxonomy;
use App\Repositories\CacheKeys;
use App\Support\Locale;
use Illuminate\Support\Facades\Redis;

/**
 * Single source проекций "БД → Redis".
 *
 * Вызывается:
 *  • из ProductObserver / TaxonomyObserver на save/delete (write-through)
 *  • из artisan-команды `catalog:warm` при деплое / scheduled
 *  • из CatalogSeeder после seed
 *
 * Все ключи — JSON-сериализованные массивы готовых к рендеру shape-ов
 * (тот же контракт, что был у App\Support\Catalog).
 */
class CacheWarmer
{
    public function __construct(private readonly ImageOptimizer $images)
    {
    }

    /**
     * Полная перезаливка проекций. Использует pipeline для атомарности
     * и минимизации round-trips.
     */
    public function warmAll(): void
    {
        $this->warmTaxonomies();
        $this->warmProducts();
    }

    public function warmProducts(): void
    {
        $products = Product::published()
            ->with(['variants', 'images', 'taxonomies', 'coa', 'reviews.images', 'questions'])
            ->orderBy('position')
            ->get();
        $taxonomyLookup = $this->taxonomyLookup();

        $slugs = [];
        $byTaxonomy = []; // [type][key] => slugs[]

        Redis::pipeline(function ($pipe) use ($products, $taxonomyLookup, &$slugs, &$byTaxonomy) {
            foreach ($products as $product) {
                $shaped = $this->shapeProduct($product, $taxonomyLookup);
                $slugs[] = $product->slug;
                $pipe->set(CacheKeys::product($product->slug), json_encode($shaped));

                foreach (['color', 'strain', 'form'] as $type) {
                    $key = $product->{$type.'_slug'};
                    if ($key) {
                        $byTaxonomy[$type][$key][] = $product->slug;
                    }
                }
            }
        });

        Redis::set(CacheKeys::productList(), json_encode($slugs));

        foreach ($byTaxonomy as $type => $keys) {
            foreach ($keys as $key => $list) {
                Redis::set(CacheKeys::catalogByTaxonomy($type, $key), json_encode($list));
            }
        }
    }

    public function warmTaxonomies(): void
    {
        $types = ['color', 'strain', 'form', 'region'];

        foreach ($types as $type) {
            $items = Taxonomy::where('type', $type)->orderBy('position')->get();
            $keys = [];
            Redis::pipeline(function ($pipe) use ($items, $type, &$keys) {
                foreach ($items as $tax) {
                    $shaped = $this->shapeTaxonomy($tax);
                    $pipe->set(CacheKeys::taxonomy($type, $tax->slug), json_encode($shaped));
                    $keys[] = $tax->slug;
                }
            });
            Redis::set(CacheKeys::taxonomyIndex($type), json_encode($keys));
        }
    }

    public function warmProduct(Product $product): void
    {
        $product->load(['variants', 'images', 'taxonomies', 'coa', 'reviews.images', 'questions']);
        $shaped = $this->shapeProduct($product, $this->taxonomyLookup());
        Redis::set(CacheKeys::product($product->slug), json_encode($shaped));

        // обновляем глобальный индекс (на случай первой публикации)
        $rawIndex = Redis::get(CacheKeys::productList());
        $index = $rawIndex ? json_decode($rawIndex, true) : [];
        if (! in_array($product->slug, $index, true)) {
            $index[] = $product->slug;
            Redis::set(CacheKeys::productList(), json_encode($index));
        }
    }

    public function evictProduct(string $slug): void
    {
        Redis::del(CacheKeys::product($slug));
        $rawIndex = Redis::get(CacheKeys::productList());
        if ($rawIndex) {
            $index = array_values(array_filter(
                json_decode($rawIndex, true),
                fn ($s) => $s !== $slug
            ));
            Redis::set(CacheKeys::productList(), json_encode($index));
        }
    }

    public function warmTaxonomy(Taxonomy $tax): void
    {
        $shaped = $this->shapeTaxonomy($tax);
        Redis::set(CacheKeys::taxonomy($tax->type, $tax->slug), json_encode($shaped));
    }

    // ─── shape methods ──────────────────────────────────────────────────

    /** @param array<string, array<string, Taxonomy>> $taxonomyLookup */
    private function shapeProduct(Product $p, array $taxonomyLookup): array
    {
        $locale = Locale::DEFAULT;
        $variants = $p->variants->keyBy('size');
        $v25 = $variants->get(25);
        $v50 = $variants->get(50);
        $vMain = $p->variants->first();
        $colorTaxonomy = $p->color_slug ? ($taxonomyLookup['color'][$p->color_slug] ?? null) : null;
        $strainTaxonomy = $p->strain_slug ? ($taxonomyLookup['strain'][$p->strain_slug] ?? null) : null;
        $formTaxonomy = $p->form_slug ? ($taxonomyLookup['form'][$p->form_slug] ?? null) : null;

        $images = $p->images->map(fn ($img) => [
            'path' => $img->path,
            'alt' => $img->alt,
            'renditions' => $img->renditions ?? $this->images->defaultRenditions($img->path),
        ])->all();

        $reviewsCollection = $p->relationLoaded('reviews') ? $p->reviews : collect();
        $publishedReviews = $reviewsCollection->filter(fn ($r) => $r->status === 'approved'
            && $r->published_at && $r->published_at->lte(now()));
        $reviewsCount = $publishedReviews->count();
        $ratingAverage = $reviewsCount > 0
            ? round($publishedReviews->avg('rating'), 1)
            : (float) $p->rating;
        $ratingDistribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($publishedReviews as $r) {
            $star = max(1, min(5, (int) $r->rating));
            $ratingDistribution[$star]++;
        }
        $verifiedCount = $publishedReviews->where('verified_purchase', true)->count();
        $previewReviews = $publishedReviews
            ->sortByDesc(fn ($r) => $r->published_at)
            ->take(20)
            ->map(fn ($r) => [
                'id'                 => $r->id,
                'author'             => $r->author_name,
                'rating'             => (int) $r->rating,
                'package'            => $r->package,
                'body'               => $r->body,
                'verified'           => (bool) $r->verified_purchase,
                'helpful'            => (int) $r->helpful_count,
                'published_at'       => $r->published_at->toIso8601String(),
                'date'               => $r->published_at->locale('cs')->diffForHumans(),
                'images'             => $r->relationLoaded('images')
                    ? $r->images->map(fn ($img) => $img->path)->values()->all()
                    : [],
            ])
            ->values()
            ->all();

        $questionsCollection = $p->relationLoaded('questions') ? $p->questions : collect();
        $publishedQuestions = $questionsCollection->filter(fn ($q) => $q->status === 'approved'
            && $q->published_at && $q->published_at->lte(now()));
        $questionsCount = $publishedQuestions->count();
        $previewQuestions = $publishedQuestions
            ->sortByDesc(fn ($q) => $q->published_at)
            ->take(20)
            ->map(fn ($q) => [
                'id'           => $q->id,
                'author'       => $q->author_name,
                'question'     => $q->question,
                'answer'       => $q->answer,
                'answered_by'  => $q->answered_by,
                'answered_at'  => $q->answered_at?->toIso8601String(),
                'helpful'      => (int) $q->helpful_count,
                'published_at' => $q->published_at->toIso8601String(),
                'date'         => $q->published_at->locale('cs')->isoFormat('D. MM. YYYY'),
            ])
            ->values()
            ->all();

        return [
            'slug' => $p->slug,
            'name' => $this->t($p->name, $locale),
            'short' => $this->t($p->short, $locale),
            'description' => $this->t($p->description, $locale),
            'color' => $p->color_slug,
            'strain' => $p->strain_slug,
            'form' => $p->form_slug,
            'colorLabel' => $this->t($colorTaxonomy?->label, $locale),
            'strainLabel' => $this->t($strainTaxonomy?->label, $locale),
            'formLabel' => $this->t($formTaxonomy?->label, $locale),
            'vein' => $colorTaxonomy?->meta['vein'] ?? null,
            'origin' => $this->t($p->origin_i18n ?? $p->origin, $locale),
            'grind' => $this->t($p->grind_i18n ?? $p->grind, $locale),
            'mitragynin' => $p->mitragynin,
            'h7mg' => $p->h7mg,
            'purity' => $p->purity,
            'batch' => $p->batch,
            'testedAt' => $p->tested_at?->format('d. m. Y'),
            'rating' => $ratingAverage,
            'reviewsCount' => $reviewsCount > 0 ? $reviewsCount : (int) $p->reviews_count,
            'questionsCount' => $questionsCount > 0 ? $questionsCount : (int) $p->questions_count,
            'ratingDistribution' => $ratingDistribution,
            'verifiedReviewsCount' => $verifiedCount,
            'reviews' => $previewReviews,
            'questions' => $previewQuestions,
            'inStock' => $p->in_stock,
            'badge' => $p->badge,
            'image' => $p->main_image ?? ($images[0]['path'] ?? null),
            'gallery' => array_column($images, 'path'),
            'imageRenditions' => $images[0]['renditions'] ?? null,
            'price25' => $v25?->price,
            'price50' => $v50?->price,
            'unit' => $vMain?->unit ?? 'g',
            'unitSize' => $vMain?->size,
            'name_i18n' => $p->name,
            'short_i18n' => $p->short,
            'description_i18n' => $p->description,
            'origin_i18n' => $p->origin_i18n,
            'grind_i18n' => $p->grind_i18n,
            'colorLabel_i18n' => $colorTaxonomy?->label,
            'strainLabel_i18n' => $strainTaxonomy?->label,
            'formLabel_i18n' => $formTaxonomy?->label,
        ];
    }

    private function shapeTaxonomy(Taxonomy $t): array
    {
        $meta = $t->meta ?? [];
        $locale = Locale::DEFAULT;

        return array_merge([
            'slug' => $t->slug,
            'label' => $this->t($t->label, $locale),
            'label_i18n' => $t->label,
            'description' => $this->t($t->description ?? [], $locale),
            'description_i18n' => $t->description,
            'h1' => $this->t($meta['h1_i18n'] ?? ($meta['h1'] ?? null), $locale),
            'origin' => $this->t($meta['origin_i18n'] ?? ($meta['origin'] ?? null), $locale),
            'dose' => $this->t($meta['dose_i18n'] ?? ($meta['dose'] ?? null), $locale),
            'sub' => $this->t($meta['sub_i18n'] ?? ($meta['sub'] ?? null), $locale),
            'h1_i18n' => $meta['h1_i18n'] ?? null,
            'origin_i18n' => $meta['origin_i18n'] ?? null,
            'dose_i18n' => $meta['dose_i18n'] ?? null,
            'sub_i18n' => $meta['sub_i18n'] ?? null,
        ], array_diff_key($meta, array_flip([
            'h1',
            'origin',
            'dose',
            'sub',
            'h1_i18n',
            'origin_i18n',
            'dose_i18n',
            'sub_i18n',
        ])));
    }

    private function t(array|string|null $field, string $locale): ?string
    {
        if (method_exists(Locale::class, 'translate')) {
            return Locale::translate($field, $locale);
        }

        if ($field === null) {
            return null;
        }

        if (is_string($field)) {
            $trimmed = trim($field);

            return $trimmed !== '' ? $trimmed : null;
        }

        $candidates = array_values(array_unique([
            $locale,
            Locale::DEFAULT,
            ...array_keys($field),
        ]));

        foreach ($candidates as $candidate) {
            $translated = $field[$candidate] ?? null;

            if (is_string($translated) && trim($translated) !== '') {
                return trim($translated);
            }
        }

        return null;
    }

    /** @return array<string, array<string, Taxonomy>> */
    private function taxonomyLookup(): array
    {
        return Taxonomy::query()
            ->get()
            ->groupBy('type')
            ->map(fn ($items) => $items->keyBy('slug')->all())
            ->all();
    }
}
