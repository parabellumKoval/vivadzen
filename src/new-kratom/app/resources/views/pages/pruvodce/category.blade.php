@php use App\Support\Locale; @endphp

<x-layouts.app
    :title="$category->title.' — průvodce kratomem | Vivadzen'"
    :description="$category->description"
    :canonical="url(Locale::url('/pruvodce/'.$category->slug))"
>
    <x-ui.breadcrumbs :items="[
        ['label' => 'Domů', 'href' => Locale::url('/')],
        ['label' => 'Průvodce', 'href' => Locale::url('/pruvodce')],
        ['label' => $category->title],
    ]" />

    <section class="pruvodce-hero pruvodce-hero--{{ $category->accent ?: 'cream' }}">
        <div class="container container--narrow">
            @if ($category->eyebrow)
                <p class="pruvodce-hero__eyebrow">{{ $category->eyebrow }}</p>
            @endif
            <h1 class="pruvodce-hero__title">{{ $category->title }}</h1>
            @if ($category->description)
                <p class="pruvodce-hero__lead">{{ $category->description }}</p>
            @endif
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

    @if ($siblings->isNotEmpty())
        <section class="pruvodce-section pruvodce-section--alt">
            <div class="container">
                <h2 class="pruvodce-section__title pruvodce-section__title--small">Další oblasti průvodce</h2>
                <div class="pruvodce-grid pruvodce-grid--3">
                    @foreach ($siblings as $sib)
                        <a class="pruvodce-sib-card" href="{{ Locale::url('/pruvodce/'.$sib->slug) }}">
                            <h3>{{ $sib->title }}</h3>
                            @if ($sib->description)
                                <p>{{ $sib->description }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
