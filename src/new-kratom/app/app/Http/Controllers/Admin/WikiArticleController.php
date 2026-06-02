<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WikiArticle;
use App\Services\MediaStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Mews\Purifier\Facades\Purifier;

/**
 * Wiki-статьи раздела /pruvodce.
 *
 * body — HTML от TipTap, санитизуется профилем 'wiki' (config/purifier.php).
 * related_ids — массив ID статей для блока «Související články».
 */
class WikiArticleController extends Controller
{
    private const BLOCKED_COMMERCIAL_TERMS = [
        'koupit', 'koupě', 'cena', 'levně', 'sleva', 'akce',
        'nejlepší', 'nejlepsi', 'doporučujeme', 'doporucujeme', 'prodej',
    ];

    public function __construct(private readonly MediaStorage $storage) {}

    public function index(Request $request): JsonResponse
    {
        $query = WikiArticle::query()
            ->with('category:id,slug,title');

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('seo_keyword', 'like', "%{$search}%");
            });
        }
        if ($categoryId = $request->integer('category_id')) {
            $query->where('wiki_category_id', $categoryId);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $articles = $query
            ->orderByDesc('updated_at')
            ->paginate($request->integer('per_page', 25));

        $articles->getCollection()->transform(fn (WikiArticle $a) => $this->shapeList($a));

        return response()->json(['data' => $articles]);
    }

    public function show(WikiArticle $article): JsonResponse
    {
        $article->load(['category:id,slug,title', 'related:id,slug,title']);

        return response()->json(['data' => $this->shapeFull($article)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePayload($request);

        $article = DB::transaction(function () use ($data) {
            $article = WikiArticle::create($data['article']);
            if (! empty($data['related_ids'])) {
                $article->related()->sync($this->relatedPivot($data['related_ids']));
            }

            return $article;
        });

        return response()->json(['data' => $this->shapeFull($article->load(['category', 'related']))], 201);
    }

    public function update(Request $request, WikiArticle $article): JsonResponse
    {
        $data = $this->validatePayload($request, $article);

        DB::transaction(function () use ($data, $article) {
            $article->fill($data['article'])->save();
            if (array_key_exists('related_ids', $data)) {
                $article->related()->sync($this->relatedPivot($data['related_ids']));
            }
        });

        return response()->json(['data' => $this->shapeFull($article->fresh(['category', 'related']))]);
    }

    public function destroy(WikiArticle $article): JsonResponse
    {
        if ($article->cover_path) {
            $this->storage->delete('public', $article->cover_path);
        }
        $article->delete();

        return response()->json(['ok' => true]);
    }

    public function publish(WikiArticle $article): JsonResponse
    {
        $article->forceFill([
            'status' => 'published',
            'published_at' => $article->published_at ?: now(),
        ])->save();

        return response()->json(['data' => $this->shapeFull($article->fresh(['category', 'related']))]);
    }

    public function unpublish(WikiArticle $article): JsonResponse
    {
        $article->forceFill(['status' => 'draft'])->save();

        return response()->json(['data' => $this->shapeFull($article->fresh(['category', 'related']))]);
    }

    public function uploadCover(Request $request, WikiArticle $article): JsonResponse
    {
        $request->validate([
            'cover' => 'required|image|mimes:jpeg,jpg,png,webp|max:4096',
            'alt' => 'nullable|string|max:255',
        ]);

        if ($article->cover_path) {
            $this->storage->delete('public', $article->cover_path);
        }

        $stored = $this->storage->store($request->file('cover'), 'wiki/'.$article->id);
        $article->forceFill([
            'cover_path' => $stored['path'],
            'cover_url' => null,
            'cover_alt' => $request->input('alt') ?: $article->cover_alt,
        ])->save();

        return response()->json(['data' => $this->shapeFull($article->fresh(['category', 'related']))]);
    }

    // ──────────── валидация и форматирование ────────────

    private function validatePayload(Request $request, ?WikiArticle $existing = null): array
    {
        $data = $request->validate([
            'wiki_category_id' => 'required|exists:wiki_categories,id',
            'slug' => [
                'required', 'string', 'max:180', 'regex:/^[a-z0-9\-]+$/',
                Rule::unique('wiki_articles', 'slug')->ignore($existing?->id),
            ],
            'title' => 'required|string|max:200',
            'excerpt' => 'nullable|string|max:320',
            'body' => 'required|string',
            'cover_url' => 'nullable|url|max:500',
            'cover_alt' => 'nullable|string|max:255',
            'seo_keyword' => 'nullable|string|max:160',
            'seo_secondary_keywords' => 'nullable|array|max:10',
            'seo_secondary_keywords.*' => 'string|max:160',
            'seo_search_intent' => 'nullable|in:informational,navigational,transactional',
            'seo_volume_estimate' => 'nullable|integer|min:0|max:1000000',
            'seo_meta_title' => 'nullable|string|max:200',
            'seo_meta_description' => 'nullable|string|max:320',
            'reading_time_minutes' => 'nullable|integer|min:1|max:120',
            'position' => 'nullable|integer|min:0|max:65535',
            'status' => 'nullable|in:draft,published',
            'published_at' => 'nullable|date',
            'related_ids' => 'nullable|array|max:8',
            'related_ids.*' => 'integer|exists:wiki_articles,id',
        ]);

        $data['body'] = Purifier::clean($data['body'], 'wiki');
        $data['reading_time_minutes'] = $data['reading_time_minutes']
            ?? $this->estimateReadingTime($data['body']);

        return [
            'article' => collect($data)
                ->except('related_ids')
                ->toArray(),
            'related_ids' => $data['related_ids'] ?? [],
        ];
    }

    private function relatedPivot(array $ids): array
    {
        $pivot = [];
        foreach ($ids as $i => $id) {
            $pivot[$id] = ['position' => $i];
        }

        return $pivot;
    }

    private function estimateReadingTime(string $html): int
    {
        $words = max(1, str_word_count(strip_tags($html)));

        return max(1, (int) ceil($words / 220));
    }

    private function shapeList(WikiArticle $a): array
    {
        return [
            'id' => $a->id,
            'slug' => $a->slug,
            'title' => $a->title,
            'category' => $a->category ? [
                'id' => $a->category->id,
                'slug' => $a->category->slug,
                'title' => $a->category->title,
            ] : null,
            'seo_keyword' => $a->seo_keyword,
            'commercial_warning' => $this->hasCommercialTerms($a),
            'status' => $a->status,
            'reading_time_minutes' => $a->reading_time_minutes,
            'updated_at' => $a->updated_at?->toISOString(),
            'published_at' => $a->published_at?->toISOString(),
        ];
    }

    private function shapeFull(WikiArticle $a): array
    {
        return array_merge($this->shapeList($a), [
            'excerpt' => $a->excerpt,
            'body' => $a->body,
            'cover_path' => $a->cover_path,
            'cover_url' => $a->cover_url,
            'cover_alt' => $a->cover_alt,
            'cover_display_url' => $a->coverDisplayUrl(),
            'seo_secondary_keywords' => $a->seo_secondary_keywords ?? [],
            'seo_search_intent' => $a->seo_search_intent,
            'seo_volume_estimate' => $a->seo_volume_estimate,
            'seo_meta_title' => $a->seo_meta_title,
            'seo_meta_description' => $a->seo_meta_description,
            'position' => $a->position,
            'related_ids' => $a->related->pluck('id')->all(),
            'related' => $a->related->map(fn ($r) => [
                'id' => $r->id, 'slug' => $r->slug, 'title' => $r->title,
            ])->all(),
        ]);
    }

    private function hasCommercialTerms(WikiArticle $a): array
    {
        $haystack = mb_strtolower($a->title.' '.$a->slug.' '.($a->seo_keyword ?? ''));
        $found = [];
        foreach (self::BLOCKED_COMMERCIAL_TERMS as $term) {
            if (str_contains($haystack, $term)) {
                $found[] = $term;
            }
        }

        return $found;
    }
}
