<?php

namespace App\Http\Controllers;

use App\Models\WikiArticle;
use App\Models\WikiCategory;
use Illuminate\View\View;

class PruvodceController extends Controller
{
    public function index(): View
    {
        $categories = WikiCategory::active()
            ->withCount(['publishedArticles as articles_count'])
            ->get();

        $featured = WikiArticle::published()
            ->with('category:id,slug,title')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        return view('pages.pruvodce.index', [
            'categories' => $categories,
            'featured' => $featured,
        ]);
    }

    public function category(string $category): View
    {
        $cat = WikiCategory::where('slug', $category)
            ->where('is_active', true)
            ->firstOrFail();

        $articles = $cat->articles()
            ->published()
            ->with('category:id,slug,title')
            ->orderBy('position')
            ->orderByDesc('published_at')
            ->get();

        $siblings = WikiCategory::active()
            ->where('id', '!=', $cat->id)
            ->get();

        return view('pages.pruvodce.category', [
            'category' => $cat,
            'articles' => $articles,
            'siblings' => $siblings,
        ]);
    }

    public function article(string $category, string $slug): View
    {
        $cat = WikiCategory::where('slug', $category)
            ->where('is_active', true)
            ->firstOrFail();

        $article = WikiArticle::with([
                'related:id,slug,title,excerpt,wiki_category_id',
                'related.category:id,slug,title',
            ])
            ->where('wiki_category_id', $cat->id)
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        WikiArticle::where('id', $article->id)->increment('views_count');

        $related = $article->related;
        if ($related->count() < 3) {
            $extra = WikiArticle::published()
                ->where('wiki_category_id', $cat->id)
                ->where('id', '!=', $article->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->with('category:id,slug,title')
                ->limit(3 - $related->count())
                ->get(['id', 'slug', 'title', 'excerpt', 'wiki_category_id']);
            $related = $related->concat($extra);
        }

        // Все статьи раздела — для бокового TOC (с подсветкой текущей).
        $categoryArticles = $cat->articles()
            ->published()
            ->orderBy('position')
            ->orderByDesc('published_at')
            ->get(['id', 'slug', 'title', 'wiki_category_id']);

        return view('pages.pruvodce.article', [
            'category' => $cat,
            'article' => $article,
            'related' => $related,
            'categoryArticles' => $categoryArticles,
        ]);
    }
}
