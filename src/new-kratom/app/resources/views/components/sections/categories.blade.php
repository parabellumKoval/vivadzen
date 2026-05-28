{{-- S5 Categories — 04_HOMEPAGE.md §7 (Image 3) --}}
@php
    $cats = [
        ['title' => 'Zelený',    'subtitle' => 'Zelená žilka',  'href' => '/kratom/zeleny',    'glyph' => 'green',  'icon' => 'leaf'],
        ['title' => 'Bílý',      'subtitle' => 'Bílá žilka',    'href' => '/kratom/bily',      'glyph' => 'white',  'icon' => 'leaf'],
        ['title' => 'Červený',   'subtitle' => 'Červená žilka', 'href' => '/kratom/cerveny',   'glyph' => 'red',    'icon' => 'leaf'],
        ['title' => 'Žlutý',     'subtitle' => 'Žlutá žilka',   'href' => '/kratom/zluty',     'glyph' => 'yellow', 'icon' => 'leaf'],
        ['title' => 'Maeng Da',  'subtitle' => 'Klasická odrůda','href' => '/kratom/maeng-da', 'glyph' => 'forest', 'icon' => 'sparkles'],
        ['title' => 'Extrakt',   'subtitle' => 'Tekutá forma',  'href' => '/kratom/extrakt',   'glyph' => 'amber',  'icon' => 'flask'],
        ['title' => 'Předplatné','subtitle' => 'Pravidelně −10 %', 'href' => '/predplatne',    'glyph' => 'terra',  'icon' => 'badge-check'],
    ];
@endphp

<section class="section section--cream" aria-labelledby="cats-title">
    <div class="container">
        <x-ui.section-head
            eyebrow="EXPLORE CATEGORIES"
            title="Najděte svůj druh kratomu"
            titleTag="h2"
            titleClass="t-display-md t-on-light-accent"
            lead="Specializovaný PML sortiment podle barvy žilky, odrůdy a formy. Každá kategorie odpovídá konkrétnímu profilu mitragyninu a způsobu zpracování."
            center
        />

        <div class="cats__grid">
            @foreach($cats as $c)
                <x-ui.category-card
                    :title="$c['title']"
                    :subtitle="$c['subtitle']"
                    :href="$c['href']"
                    :glyph="$c['glyph']"
                    :icon="$c['icon']"
                />
            @endforeach
        </div>

        <div class="flex justify-center mt-12">
            <x-ui.button href="/kratom/odrudy" variant="text" icon="arrow-right">
                Zobrazit všechny odrůdy
            </x-ui.button>
        </div>
    </div>
</section>
