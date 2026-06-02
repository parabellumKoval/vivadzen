# PHASE 04 — Публичный фронт (Blade)

> Зависит от: PHASE-02 (модели и БД). PHASE-03 не обязательна — фронт может
> рендерить даже статьи, созданные через tinker.
> Время реализации: 2–3 часа.

## Что нужно сделать

1. Контроллер `PruvodceController` с тремя методами:
   `index()` (landing), `category(slug)`, `article(catSlug, articleSlug)`.
2. Маршруты в `routes/web.php` — заменить старый одиночный `/pruvodce`.
3. Blade-шаблоны:
   - `pages/pruvodce/index.blade.php` — landing
   - `pages/pruvodce/category.blade.php` — каталог категории
   - `pages/pruvodce/article.blade.php` — статья
   - `components/pruvodce/wiki-card.blade.php` — карточка без обложки
   - `components/pruvodce/related-block.blade.php` — блок «Související
     články»
4. SCSS `resources/scss/pages/_pruvodce.scss`, подключить в `app.scss`.
5. Удалить старую `pages/static/guide.blade.php` (на этом этапе или после
   PHASE-07 — на твой выбор).

---

## 1. Маршруты

В `src/new-kratom/app/routes/web.php`, внутри замыкания `$register`,
**заменить** строку:

```php
Route::get('/pruvodce', [PageController::class, 'guide'])->name('page.guide');
```

на:

```php
// Pruvodce (wiki)
Route::get('/pruvodce', [PruvodceController::class, 'index'])->name('pruvodce.index');
Route::get('/pruvodce/{category}', [PruvodceController::class, 'category'])
    ->where('category', '[a-z0-9\-]+')->name('pruvodce.category');
Route::get('/pruvodce/{category}/{slug}', [PruvodceController::class, 'article'])
    ->where(['category' => '[a-z0-9\-]+', 'slug' => '[a-z0-9\-]+'])
    ->name('pruvodce.article');
```

И в `use`-секции наверху файла:
```php
use App\Http\Controllers\PruvodceController;
```

Метод `PageController::guide()` можно оставить — он больше не вызывается.
Удалим в финальной фазе.

---

## 2. Контроллер

**Файл:** `src/new-kratom/app/app/Http/Controllers/PruvodceController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\WikiArticle;
use App\Models\WikiCategory;
use Illuminate\Http\Request;
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

        // increment views_count (best-effort, без блокировок)
        WikiArticle::where('id', $article->id)->increment('views_count');

        // Если в БД < 3 связанных — добивaем «другими статьями той же категории»
        $related = $article->related;
        if ($related->count() < 3) {
            $extra = WikiArticle::published()
                ->where('wiki_category_id', $cat->id)
                ->where('id', '!=', $article->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->limit(3 - $related->count())
                ->get(['id', 'slug', 'title', 'excerpt', 'wiki_category_id'])
                ->load('category:id,slug,title');
            $related = $related->concat($extra);
        }

        return view('pages.pruvodce.article', [
            'category' => $cat,
            'article' => $article,
            'related' => $related,
        ]);
    }
}
```

---

## 3. Шаблоны Blade

### 3.1 `resources/views/pages/pruvodce/index.blade.php` — landing

```blade
@php use App\Support\Locale; @endphp

<x-layouts.app
    title="Průvodce kratomem — wiki encyklopedie | Vivadzen"
    description="Věcný průvodce kratomem (Mitragyna speciosa): botanika, chemie, legislativa ČR 2026, kvalita a bezpečnost. Bez marketingu, bez doporučení k užívání."
    canonical="{{ Locale::url('/pruvodce', absolute: true) }}"
>
    <x-ui.breadcrumbs :items="[
        ['label' => 'Domů', 'href' => Locale::url('/')],
        ['label' => 'Průvodce'],
    ]" />

    <section class="pruvodce-hero pruvodce-hero--cream">
        <div class="container container--narrow">
            <p class="pruvodce-hero__eyebrow">Wiki encyklopedie</p>
            <h1 class="pruvodce-hero__title">Průvodce kratomem</h1>
            <p class="pruvodce-hero__lead">
                Strukturovaný a věcný přehled toho, co je kratom, jak jeho rostlina
                vypadá, jaké alkaloidy obsahuje, jak je v ČR od roku 2026 regulován
                a jak se posuzuje jeho kvalita. Bez marketingu a bez doporučení.
            </p>
        </div>
    </section>

    <section class="pruvodce-section">
        <div class="container">
            <h2 class="pruvodce-section__title">Hlavní oblasti</h2>
            <div class="pruvodce-grid pruvodce-grid--4">
                @foreach ($categories as $cat)
                    <a class="pruvodce-cat-card pruvodce-cat-card--{{ $cat->accent }}"
                       href="{{ Locale::url('/pruvodce/'.$cat->slug) }}">
                        <p class="pruvodce-cat-card__eyebrow">{{ $cat->eyebrow }}</p>
                        <h3 class="pruvodce-cat-card__title">{{ $cat->title }}</h3>
                        <p class="pruvodce-cat-card__desc">{{ $cat->description }}</p>
                        <span class="pruvodce-cat-card__meta">{{ $cat->articles_count }} článků →</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @if($featured->isNotEmpty())
        <section class="pruvodce-section pruvodce-section--alt">
            <div class="container">
                <h2 class="pruvodce-section__title">Naposledy publikováno</h2>
                <div class="pruvodce-grid pruvodce-grid--3">
                    @foreach ($featured as $a)
                        <x-pruvodce.wiki-card :article="$a" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="pruvodce-section pruvodce-section--note">
        <div class="container container--narrow">
            <p class="pruvodce-note">
                Tento průvodce je informační a nepředstavuje doporučení k užívání.
                Kratom je v České republice od roku 2026 regulován jako
                <a href="{{ Locale::url('/pruvodce/legislativa-cr/psychomodulacni-latky') }}">psychomodulační látka</a>
                ve smyslu zákona 167/1998 Sb.
            </p>
        </div>
    </section>
</x-layouts.app>
```

### 3.2 `resources/views/pages/pruvodce/category.blade.php`

```blade
@php use App\Support\Locale; @endphp

<x-layouts.app
    :title="$category->title . ' — průvodce kratomem | Vivadzen'"
    :description="$category->description"
    :canonical="Locale::url('/pruvodce/'.$category->slug, absolute: true)"
>
    <x-ui.breadcrumbs :items="[
        ['label' => 'Domů', 'href' => Locale::url('/')],
        ['label' => 'Průvodce', 'href' => Locale::url('/pruvodce')],
        ['label' => $category->title],
    ]" />

    <section class="pruvodce-hero pruvodce-hero--{{ $category->accent }}">
        <div class="container container--narrow">
            <p class="pruvodce-hero__eyebrow">{{ $category->eyebrow }}</p>
            <h1 class="pruvodce-hero__title">{{ $category->title }}</h1>
            <p class="pruvodce-hero__lead">{{ $category->description }}</p>
        </div>
    </section>

    <section class="pruvodce-section">
        <div class="container">
            @if ($articles->isEmpty())
                <p class="pruvodce-empty">V této kategorii zatím nejsou publikované články.</p>
            @else
                <div class="pruvodce-grid pruvodce-grid--3">
                    @foreach ($articles as $a)
                        <x-pruvodce.wiki-card :article="$a" :showCategory="false" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="pruvodce-section pruvodce-section--alt">
        <div class="container">
            <h2 class="pruvodce-section__title pruvodce-section__title--small">Další oblasti průvodce</h2>
            <div class="pruvodce-grid pruvodce-grid--3">
                @foreach ($siblings as $sib)
                    <a class="pruvodce-sib-card" href="{{ Locale::url('/pruvodce/'.$sib->slug) }}">
                        <h3>{{ $sib->title }}</h3>
                        <p>{{ $sib->description }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.app>
```

### 3.3 `resources/views/pages/pruvodce/article.blade.php`

```blade
@php use App\Support\Locale; @endphp

<x-layouts.app
    :title="$article->metaTitle()"
    :description="$article->metaDescription()"
    :canonical="Locale::url('/pruvodce/'.$category->slug.'/'.$article->slug, absolute: true)"
>
    <x-ui.breadcrumbs :items="[
        ['label' => 'Domů', 'href' => Locale::url('/')],
        ['label' => 'Průvodce', 'href' => Locale::url('/pruvodce')],
        ['label' => $category->title, 'href' => Locale::url('/pruvodce/'.$category->slug)],
        ['label' => $article->title],
    ]" />

    <article class="pruvodce-article">
        <header class="pruvodce-article__head container container--narrow">
            <p class="pruvodce-article__eyebrow">
                <a href="{{ Locale::url('/pruvodce/'.$category->slug) }}">{{ $category->title }}</a>
            </p>
            <h1 class="pruvodce-article__title">{{ $article->title }}</h1>

            @if ($article->excerpt)
                <p class="pruvodce-article__lead">{{ $article->excerpt }}</p>
            @endif

            <ul class="pruvodce-article__meta">
                @if ($article->reading_time_minutes)
                    <li>{{ $article->reading_time_minutes }} min čtení</li>
                @endif
                @if ($article->published_at)
                    <li>Publikováno {{ $article->published_at->format('j. n. Y') }}</li>
                @endif
            </ul>
        </header>

        <div class="container container--narrow">
            <div class="prose pruvodce-article__body">
                {!! $article->body !!}
            </div>
        </div>

        <x-pruvodce.related-block :articles="$related" />

        <div class="container container--narrow">
            <p class="pruvodce-article__catalog-note">
                Hledáte konkrétní produkt? Veškerou nabídku najdete v
                <a href="{{ Locale::url('/kratom') }}">katalogu</a>.
            </p>
        </div>
    </article>

    {{-- JSON-LD Article schema --}}
    <script type="application/ld+json">
        @json([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $article->excerpt,
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at?->toIso8601String(),
            'inLanguage' => 'cs',
            'mainEntityOfPage' => Locale::url('/pruvodce/'.$category->slug.'/'.$article->slug, absolute: true),
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Vivadzen Průvodce',
                'url' => Locale::url('/pruvodce', absolute: true),
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    </script>
</x-layouts.app>
```

### 3.4 `resources/views/components/pruvodce/wiki-card.blade.php`

```blade
@php use App\Support\Locale; @endphp
@props(['article', 'showCategory' => true])

<a class="pruvodce-card"
   href="{{ Locale::url('/pruvodce/'.$article->category->slug.'/'.$article->slug) }}">
    @if ($showCategory)
        <p class="pruvodce-card__eyebrow">{{ $article->category->title }}</p>
    @endif
    <h3 class="pruvodce-card__title">{{ $article->title }}</h3>
    @if ($article->excerpt)
        <p class="pruvodce-card__desc">{{ $article->excerpt }}</p>
    @endif
    @if ($article->reading_time_minutes)
        <p class="pruvodce-card__meta">{{ $article->reading_time_minutes }} min čtení</p>
    @endif
</a>
```

### 3.5 `resources/views/components/pruvodce/related-block.blade.php`

```blade
@php use App\Support\Locale; @endphp
@props(['articles'])

@if ($articles->isNotEmpty())
    <aside class="container container--narrow pruvodce-related">
        <h2 class="pruvodce-related__title">Související články</h2>
        <ul class="pruvodce-related__list">
            @foreach ($articles as $r)
                <li>
                    <a href="{{ Locale::url('/pruvodce/'.$r->category->slug.'/'.$r->slug) }}">
                        <span class="pruvodce-related__cat">{{ $r->category->title }}</span>
                        <span class="pruvodce-related__heading">{{ $r->title }}</span>
                        @if ($r->excerpt)
                            <span class="pruvodce-related__desc">{{ $r->excerpt }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>
@endif
```

---

## 4. SCSS

**Файл:** `resources/scss/pages/_pruvodce.scss` (создать).

Ключевые принципы:
- **Карточки без обложек.** Только текст, тонкая граница, кремовый фон.
- Hero — приглушённый, без больших картинок: только цветной accent-полоской
  сверху и eyebrow + title + lead.
- prose уже стилизован — переиспользуем (`@extend .prose` или просто класс).

```scss
@use '../tokens' as *;

.pruvodce-hero {
    padding: clamp(48px, 8vw, 96px) 0 32px;
    border-bottom: 1px solid var(--c-ink-700-10);

    &__eyebrow {
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-size: 0.78rem;
        color: var(--c-forest-700);
        margin: 0 0 8px;
    }
    &__title {
        font-family: var(--ff-display);
        font-size: clamp(2rem, 3.5vw, 2.75rem);
        margin: 0 0 12px;
        color: var(--c-ink-900);
    }
    &__lead {
        font-size: 1.05rem;
        line-height: 1.55;
        color: var(--c-ink-700);
        max-width: 64ch;
    }

    &--grass  &__eyebrow { color: var(--c-grass-500); }
    &--terra  &__eyebrow { color: var(--c-terracotta-500); }
    &--amber  &__eyebrow { color: var(--c-amber-500); }
    &--cream  &__eyebrow { color: var(--c-forest-700); }
    &--moss   &__eyebrow { color: var(--c-moss-700); }
}

.pruvodce-section {
    padding: clamp(40px, 6vw, 64px) 0;
    &--alt { background: var(--c-cream-50); }
    &--note { padding: 24px 0 64px; }

    &__title {
        font-family: var(--ff-display);
        font-size: 1.5rem;
        margin: 0 0 24px;
        color: var(--c-ink-900);
        &--small { font-size: 1.25rem; }
    }
}

.pruvodce-grid {
    display: grid;
    gap: 16px;
    &--3 { grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
    &--4 { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
}

.pruvodce-cat-card,
.pruvodce-card,
.pruvodce-sib-card {
    display: block;
    text-decoration: none;
    color: var(--c-ink-900);
    background: #fff;
    border: 1px solid var(--c-ink-700-10);
    border-radius: 10px;
    padding: 20px 22px;
    transition: border-color 0.15s, transform 0.15s;

    &:hover {
        border-color: var(--c-forest-700);
        transform: translateY(-1px);
    }
}

.pruvodce-cat-card {
    border-left: 4px solid var(--c-forest-700);
    &--grass  { border-left-color: var(--c-grass-500); }
    &--terra  { border-left-color: var(--c-terracotta-500); }
    &--amber  { border-left-color: var(--c-amber-500); }
    &--cream  { border-left-color: var(--c-cream-300); }
    &--moss   { border-left-color: var(--c-moss-700); }

    &__eyebrow {
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-size: 0.7rem;
        color: var(--c-ink-700);
        margin: 0 0 4px;
    }
    &__title { font-family: var(--ff-display); font-size: 1.15rem; margin: 0 0 8px; }
    &__desc  { font-size: 0.92rem; color: var(--c-ink-700); margin: 0 0 12px; line-height: 1.45; }
    &__meta  { font-size: 0.78rem; color: var(--c-forest-700); }
}

.pruvodce-card {
    &__eyebrow {
        font-size: 0.7rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--c-forest-700);
        margin: 0 0 6px;
    }
    &__title { font-family: var(--ff-display); font-size: 1.1rem; margin: 0 0 8px; line-height: 1.3; }
    &__desc  { font-size: 0.9rem; color: var(--c-ink-700); line-height: 1.5; margin: 0 0 10px; }
    &__meta  { font-size: 0.75rem; color: var(--c-ink-700-60); margin: 0; }
}

.pruvodce-sib-card {
    h3 { font-family: var(--ff-display); font-size: 1.05rem; margin: 0 0 6px; }
    p  { font-size: 0.88rem; color: var(--c-ink-700); margin: 0; line-height: 1.45; }
}

.pruvodce-empty {
    padding: 40px;
    text-align: center;
    color: var(--c-ink-700-60);
    font-style: italic;
}

.pruvodce-article {
    padding: clamp(40px, 6vw, 64px) 0;

    &__head {
        margin-bottom: 32px;
    }
    &__eyebrow {
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-size: 0.75rem;
        margin: 0 0 8px;
        a { color: var(--c-forest-700); text-decoration: none; }
    }
    &__title {
        font-family: var(--ff-display);
        font-size: clamp(1.75rem, 3.2vw, 2.5rem);
        line-height: 1.2;
        margin: 0 0 14px;
    }
    &__lead {
        font-size: 1.1rem;
        line-height: 1.55;
        color: var(--c-ink-700);
        margin: 0 0 12px;
    }
    &__meta {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        gap: 18px;
        font-size: 0.82rem;
        color: var(--c-ink-700-60);
        li { display: inline; }
    }
    &__body {
        padding: 16px 0 32px;
    }
    &__catalog-note {
        margin: 48px auto 0;
        padding: 14px 18px;
        border: 1px dashed var(--c-ink-700-10);
        border-radius: 8px;
        color: var(--c-ink-700-60);
        font-size: 0.85rem;
        text-align: center;
        a { color: var(--c-forest-700); }
    }
}

.pruvodce-related {
    margin-top: 48px;
    padding: 32px;
    background: var(--c-cream-50);
    border-radius: 12px;

    &__title {
        font-family: var(--ff-display);
        font-size: 1.3rem;
        margin: 0 0 18px;
    }
    &__list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    }
    &__list a {
        display: block;
        padding: 14px 16px;
        background: #fff;
        border-radius: 8px;
        text-decoration: none;
        color: var(--c-ink-900);
        border: 1px solid transparent;
        &:hover { border-color: var(--c-forest-700); }
    }
    &__cat {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--c-forest-700);
        margin: 0 0 4px;
    }
    &__heading {
        display: block;
        font-family: var(--ff-display);
        font-size: 1rem;
        margin: 0 0 6px;
    }
    &__desc {
        display: block;
        font-size: 0.84rem;
        color: var(--c-ink-700);
        line-height: 1.45;
    }
}

.pruvodce-note {
    font-size: 0.88rem;
    color: var(--c-ink-700);
    border-left: 3px solid var(--c-amber-500);
    padding-left: 14px;
    line-height: 1.55;
    a { color: var(--c-forest-700); }
}
```

В `resources/scss/app.scss` добавь импорт (рядом с другими `pages/*`):

```scss
@use 'pages/pruvodce';
```

> Если в существующих токенах нет `--c-ink-700-10` или `--c-ink-700-60` —
> возьми ближайший аналог из `_static.scss` (там есть `rgba(42,42,42,...)`).
> Главное — не вводить хардкодные HEX, всё через CSS-переменные.

---

## 5. Что делать со старой `guide.blade.php`

Файл `resources/views/pages/static/guide.blade.php` больше не вызывается
маршрутом — старый маршрут заменён в `routes/web.php`. Можно:

- **Оставить как backup** на 1 спринт (на случай быстрого отката).
- **Удалить** после PHASE-07. Не забыть удалить также метод `guide()` из
  `PageController`.

---

## 6. Проверка (Definition of Done)

```bash
cd src/new-kratom/app
php artisan route:list --path=pruvodce
# Должны быть три маршрута без локали + ещё три с {locale}

# Smoke-test через tinker — создадим одну статью:
php artisan tinker --execute='
$cat = App\Models\WikiCategory::where("slug", "botanika-a-veda")->first();
App\Models\WikiArticle::create([
    "wiki_category_id" => $cat->id,
    "slug" => "co-je-kratom",
    "title" => "Co je kratom — botanická definice a původ",
    "excerpt" => "Kratom je tropický strom Mitragyna speciosa z čeledi mořenovitých.",
    "body" => "<h2>Definice</h2><p>Kratom (<em>Mitragyna speciosa</em>) je tropický strom.</p>",
    "seo_keyword" => "co je kratom",
    "status" => "published",
    "published_at" => now(),
]);
'

# Билдим фронт-стили:
cd src/new-kratom/app
npm run build  # или npm run dev — должно собраться без ошибок _pruvodce.scss

# Запустить локально и проверить:
# 1) /pruvodce — landing с 4 категориями + 1 опубликованной карточкой
# 2) /pruvodce/botanika-a-veda — список с 1 карточкой
# 3) /pruvodce/botanika-a-veda/co-je-kratom — статья с breadcrumbs, prose,
#    блоком «Související články» (пустой пока), JSON-LD в исходнике страницы
```

Коммит:
```
git add -A && git commit -m "pruvodce-phase-04: public frontend (landing, category, article)"
```

После этой фазы базовая инфраструктура готова. Дальше — наполнение
контентом (фазы 05–07).
