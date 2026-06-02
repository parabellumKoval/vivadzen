{{-- S10 Content hub — мини-версия /pruvodce на главной. --}}
@php
    use App\Models\WikiCategory;
    use App\Support\Locale;

    $hubCategories = WikiCategory::active()
        ->withCount(['publishedArticles as articles_count'])
        ->limit(4)
        ->get();
@endphp

@if($hubCategories->isNotEmpty())
<section class="section section--cream contenthub" aria-labelledby="hub-title">
    <div class="container">
        <x-ui.section-head
            eyebrow="WIKI ENCYKLOPEDIE"
            eyebrowVariant="soft"
            title="Co stojí za to vědět o kratomu"
            titleTag="h2"
            titleClass="t-heading-xl t-on-light-accent"
            center
        />

        <div class="pruvodce-grid pruvodce-grid--4 contenthub__grid">
            @foreach($hubCategories as $cat)
                <a class="pruvodce-cat-card pruvodce-cat-card--{{ $cat->accent ?: 'cream' }}"
                   href="{{ Locale::url('/pruvodce/'.$cat->slug) }}">
                    <x-pruvodce.cat-icon :slug="$cat->slug" />

                    @if ($cat->eyebrow)
                        <p class="pruvodce-cat-card__eyebrow">{{ $cat->eyebrow }}</p>
                    @endif
                    <h3 class="pruvodce-cat-card__title">{{ $cat->title }}</h3>
                    @if ($cat->description)
                        <p class="pruvodce-cat-card__desc">{{ $cat->description }}</p>
                    @endif
                    <span class="pruvodce-cat-card__meta">{{ $cat->articles_count }} článků →</span>
                </a>
            @endforeach
        </div>

        <div class="contenthub__cta">
            <x-ui.button :href="Locale::url('/pruvodce')" variant="outline-light" icon="arrow-right">
                Všechny průvodce
            </x-ui.button>
        </div>
    </div>
</section>
@endif
