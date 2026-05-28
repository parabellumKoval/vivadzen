{{-- S9 «Trusted by Thousands» — 04_HOMEPAGE.md §11 (Image 2) --}}
@php
    $reviews = [
        ['name' => 'Martin K.',  'date' => 'březen 2026',  'chip' => 'Zelená Maeng Da', 'quote' => 'Profesionální balení, dodání druhý den. Šarže má COA. Důvěryhodný obchod.'],
        ['name' => 'Tereza N.',  'date' => 'únor 2026',    'chip' => 'Bílý slon',       'quote' => 'Konečně český obchod, který otevřeně publikuje lab-výsledky. Vrátím se.'],
        ['name' => 'Jakub H.',   'date' => 'únor 2026',    'chip' => 'Extrakt 10 ml',    'quote' => 'Vyzkoušel jsem mnoho českých obchodů, Vivadzen je férový a transparentní.'],
        ['name' => 'Petra V.',   'date' => 'leden 2026',   'chip' => 'Zelený thajský',  'quote' => 'Vynikající kvalita, navíc kamenná prodejna v Praze. Personál odborný.'],
        ['name' => 'David B.',   'date' => 'leden 2026',   'chip' => 'Červená Maeng Da','quote' => 'Vivadzen má top kvalitu, lab-testy publikované, COA otevřeně. Slušná akce.'],
    ];
@endphp

<section class="section section--dark trusted bg-botanical" aria-labelledby="trusted-title">
    <div class="container">
        <header class="trusted__head section-head--center">
            <div class="trusted__icon-bubble"><x-ui.icon name="sparkles" /></div>
            <p class="t-overline section-head__eyebrow--grass">REAL PEOPLE, REAL RESULTS</p>
            <h2 id="trusted-title" class="t-display-md t-on-dark mt-3">Důvěřuje nám tisíce zákazníků</h2>
            <div class="trusted__rating">
                <span class="stars" aria-hidden="true">★★★★★</span>
                <span>4,9 z 5 — 2 500+ hodnocení</span>
            </div>
        </header>

        <div class="trusted__grid">
            @foreach($reviews as $r)
                <x-ui.review-card
                    :quote="$r['quote']"
                    :name="$r['name']"
                    :date="$r['date']"
                    :chip="$r['chip']"
                />
            @endforeach
        </div>

        <div class="trusted__cta">
            <x-ui.button href="/recenze" variant="secondary" size="lg" icon="arrow-right">
                Číst všechny recenze
            </x-ui.button>
        </div>
    </div>
</section>
