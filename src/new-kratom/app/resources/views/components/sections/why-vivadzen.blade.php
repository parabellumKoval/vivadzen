{{-- S7 «Proč Vivadzen» — 04_HOMEPAGE.md §9 --}}
@php
    $items = [
        [
            'icon' => 'flask',
            'title' => 'Akreditovaná laboratoř VŠCHT Praha',
            'body' => 'Každá šarže prochází nezávislým testováním v laboratoři VŠCHT akreditované dle ISO 17025 — obsah mitragyninu, čistota, mikrobiologie, těžké kovy.',
        ],
        [
            'icon' => 'shield-check',
            'title' => 'Autorizovaný prodejce PML',
            'body' => 'Vivadzen působí v režimu psychomodulačních látek (zák. č. 167/1998 Sb., novelizován č. 321/2024 Sb.) pod přímou licencí Ministerstva zdravotnictví ČR.',
        ],
        [
            'icon' => 'store',
            'title' => 'Dvě kamenné prodejny v Praze',
            'body' => 'Vinohrady a Karlín — osobní odběr, ověření věku, konzultace s vyškoleným personálem. Otevřeno 6 dní v týdnu.',
        ],
        [
            'icon' => 'zap',
            'title' => 'EXPRESS doručení 180 minut',
            'body' => 'Doručíme do 3 hodin v Praze a Ostravě pomocí vlastních messenger-kurýrů. Příplatek 299 Kč. Po celé ČR — 1–2 dny.',
        ],
    ];
@endphp

<section class="section section--dark why bg-botanical" aria-labelledby="why-title">
    <div class="container">
        <x-ui.section-head
            eyebrow="NAŠE STANDARDY"
            eyebrowVariant=""
            title="Proč Vivadzen?"
            titleTag="h2"
            titleClass="t-display-md t-on-dark"
            center
        />

        <div class="why__grid">
            @foreach($items as $i)
                <div class="why__item">
                    <span class="why__icon"><x-ui.icon :name="$i['icon']" :size="40" /></span>
                    <h3 class="why__title">{{ $i['title'] }}</h3>
                    <p class="why__body">{{ $i['body'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="why__cta">
            <x-ui.button href="/laboratorni-testy" variant="secondary" size="lg" icon="arrow-right">
                Více o naší kvalitě
            </x-ui.button>
        </div>
    </div>
</section>
