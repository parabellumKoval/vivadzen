{{-- S10 Content hub promo — 04_HOMEPAGE.md §12 --}}
@php
    $guides = [
        [
            'tag' => 'PRŮVODCE', 'tagVariant' => 'tag',
            'title' => 'Co je kratom — vědecký pohled',
            'excerpt' => 'Mitragyna speciosa, mitragynin, historie a botanika.',
            'href' => '/pruvodce/co-je-kratom',
            'placeholderHint' => '/assets/ai-generated/guides/cs-co-je-kratom-1200x800.avif',
        ],
        [
            'tag' => 'LEGISLATIVA', 'tagVariant' => 'tag-amber',
            'title' => 'Kratom v ČR — legislativa 2026',
            'excerpt' => 'Zákon č. 321/2024 Sb., PML režim a kdo má licenci.',
            'href' => '/pruvodce/kratom-zakon-2026',
            'placeholderHint' => '/assets/ai-generated/guides/cs-legislativa-1200x800.avif',
        ],
        [
            'tag' => 'KVALITA', 'tagVariant' => 'tag-terra',
            'title' => 'Jak poznat kvalitní kratom — laboratorní testy a COA',
            'excerpt' => 'Co hledat v COA, čistota, mitragynin a šarže.',
            'href' => '/pruvodce/jak-poznat-kvalitni-kratom',
            'placeholderHint' => '/assets/ai-generated/guides/cs-kvalita-coa-1200x800.avif',
        ],
    ];
@endphp

<section class="section section--cream contenthub" aria-labelledby="hub-title">
    <div class="container">
        <x-ui.section-head
            eyebrow="PRŮVODCE & FAKTA"
            eyebrowVariant="soft"
            title="Co stojí za to vědět o kratomu"
            titleTag="h2"
            titleClass="t-heading-xl t-on-light-accent"
            center
        />

        <div class="contenthub__grid">
            @foreach($guides as $g)
                <a href="{{ $g['href'] }}" class="contenthub__card">
                    <x-ui.placeholder shape="wide" :hint="$g['placeholderHint']" label="Obrázek průvodce" icon="leaf" />
                    <div class="contenthub__body">
                        <x-ui.badge :variant="$g['tagVariant']">{{ $g['tag'] }}</x-ui.badge>
                        <h3 class="contenthub__title">{{ $g['title'] }}</h3>
                        <p class="contenthub__excerpt">{{ $g['excerpt'] }}</p>
                        <span class="contenthub__more">Číst →</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="contenthub__cta">
            <x-ui.button href="/pruvodce" variant="outline-light" icon="arrow-right">
                Všechny průvodce
            </x-ui.button>
        </div>
    </div>
</section>
