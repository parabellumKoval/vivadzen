@php use App\Support\Locale; @endphp

<x-layouts.app
    :title="$article->metaTitle()"
    :description="$article->metaDescription()"
    :canonical="url(Locale::url('/pruvodce/'.$category->slug.'/'.$article->slug))"
>
    <x-ui.breadcrumbs :items="[
        ['label' => 'Domů', 'href' => Locale::url('/')],
        ['label' => 'Průvodce', 'href' => Locale::url('/pruvodce')],
        ['label' => $category->title, 'href' => Locale::url('/pruvodce/'.$category->slug)],
        ['label' => $article->title],
    ]" />

    <article class="pruvodce-article pruvodce-article--with-toc">
        <div class="container pruvodce-article__layout">
            <x-pruvodce.sidebar-toc
                :category="$category"
                :articles="$categoryArticles ?? collect()"
                :current-slug="$article->slug"
            />

            <div class="pruvodce-article__main">
                <header class="pruvodce-article__head">
                    <p class="pruvodce-article__eyebrow">
                        <a href="{{ Locale::url('/pruvodce/'.$category->slug) }}">{{ $category->title }}</a>
                    </p>
                    <h1 class="pruvodce-article__title">{{ $article->title }}</h1>

                    @if ($article->excerpt)
                        <p class="pruvodce-article__lead">{{ $article->excerpt }}</p>
                    @endif

                    @if ($article->reading_time_minutes || $article->published_at)
                        <ul class="pruvodce-article__meta">
                            @if ($article->reading_time_minutes)
                                <li>{{ $article->reading_time_minutes }} min čtení</li>
                            @endif
                            @if ($article->published_at)
                                <li>Publikováno {{ $article->published_at->format('j. n. Y') }}</li>
                            @endif
                        </ul>
                    @endif
                </header>

                <div class="prose pruvodce-article__body">
                    {!! $article->body !!}
                </div>

                <x-pruvodce.related-block :articles="$related" />

                <p class="pruvodce-article__catalog-note">
                    Hledáte konkrétní produkt? Veškerou nabídku najdete v
                    <a href="{{ Locale::url('/kratom') }}">katalogu</a>.
                </p>
            </div>
        </div>
    </article>

    @push('schema')
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $article->title,
                'description' => $article->excerpt,
                'datePublished' => $article->published_at?->toIso8601String(),
                'dateModified' => $article->updated_at?->toIso8601String(),
                'inLanguage' => 'cs',
                'mainEntityOfPage' => url(Locale::url('/pruvodce/'.$category->slug.'/'.$article->slug)),
                'isPartOf' => [
                    '@type' => 'WebSite',
                    'name' => 'Vivadzen Průvodce',
                    'url' => url(Locale::url('/pruvodce')),
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endpush
</x-layouts.app>
