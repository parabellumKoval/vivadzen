{{--
    /kratom — главный каталог (флагман)
    Соответствует vivadzen-design-tz/05_STRANKA_KATALOG_KATEGORIE.md §1.
--}}

@php
    use App\Support\Catalog;
    use App\Support\Locale;

    $products     = Catalog::products();
    $placeholders = Catalog::placeholders();
    $colors       = Catalog::colors();
    $strains      = Catalog::strains();

    $crossLinkColors = collect($colors)->map(fn ($c) => array_merge($c, [
        'count' => collect($products)->where('color', $c['slug'])->count(),
        'href'  => Locale::url('/kratom/' . $c['slug']),
    ]))->all();

    $crossLinkStrains = collect($strains)->map(fn ($s) => array_merge($s, [
        'href' => Locale::url('/kratom/' . $s['slug']),
    ]))->all();

    $heroStats = [
        ['label' => 'Šarží testovaných', 'value' => '100 %'],
        ['label' => 'Aktivních odrůd',    'value' => count($products)],
        ['label' => 'Prodejen v Praze',   'value' => '2'],
        ['label' => 'COA dostupných',     'value' => 'ANO'],
    ];

    $chips = [
        ['value' => 'all',      'label' => 'Vše'],
        ['value' => 'zeleny',   'label' => 'Zelený'],
        ['value' => 'bily',     'label' => 'Bílý'],
        ['value' => 'cerveny',  'label' => 'Červený'],
        ['value' => 'zluty',    'label' => 'Žlutý'],
        ['value' => 'maeng-da', 'label' => 'Maeng Da'],
        ['value' => 'extrakt',  'label' => 'Extrakt'],
    ];

    $filterGroups = [
        [
            'title' => 'Barva žilky',
            'open'  => true,
            'key'   => 'color',
            'options' => [
                ['label' => 'Zelený',  'value' => 'zeleny',  'vein' => 'green',  'count' => collect($products)->where('color', 'zeleny')->count()],
                ['label' => 'Bílý',    'value' => 'bily',    'vein' => 'white',  'count' => collect($products)->where('color', 'bily')->count()],
                ['label' => 'Červený', 'value' => 'cerveny', 'vein' => 'red',    'count' => collect($products)->where('color', 'cerveny')->count()],
                ['label' => 'Žlutý',   'value' => 'zluty',   'vein' => 'yellow', 'count' => collect($products)->where('color', 'zluty')->count(), 'disabled' => collect($products)->where('color', 'zluty')->isEmpty()],
            ],
        ],
        [
            'title' => 'Odrůda',
            'open'  => true,
            'key'   => 'strain',
            'options' => [
                ['label' => 'Maeng Da', 'value' => 'maeng-da', 'count' => collect($products)->where('strain', 'maeng-da')->count()],
                ['label' => 'Sumatra',  'value' => 'sumatra',  'count' => collect($products)->where('strain', 'sumatra')->count()],
                ['label' => 'Thajský',  'value' => 'thajsky',  'count' => collect($products)->where('strain', 'thajsky')->count()],
                ['label' => 'Slon',     'value' => 'slon',     'count' => collect($products)->where('strain', 'slon')->count()],
                ['label' => 'Borneo',   'value' => 'borneo',   'count' => collect($products)->where('strain', 'borneo')->count(), 'disabled' => collect($products)->where('strain', 'borneo')->isEmpty()],
                ['label' => 'Rurut',    'value' => 'rurut',    'count' => collect($products)->where('strain', 'rurut')->count()],
            ],
        ],
        [
            'title' => 'Forma',
            'key'   => 'form',
            'options' => [
                ['label' => 'Prášek',  'value' => 'prasek',  'count' => collect($products)->where('form', 'prasek')->count()],
                ['label' => 'Extrakt', 'value' => 'extrakt', 'count' => collect($products)->where('form', 'extrakt')->count()],
                ['label' => 'Nano',    'value' => 'nano',    'count' => collect($products)->where('form', 'nano')->count()],
            ],
        ],
        ['title' => 'Balení', 'options' => [
            ['label' => '25 g', 'count' => count($products)],
            ['label' => '50 g', 'count' => count($products) - 1],
        ]],
        ['title' => 'Mitragynin %', 'type' => 'range', 'min' => '1,00 %', 'max' => '12,00 %'],
        ['title' => 'Dostupnost', 'open' => true, 'options' => [
            ['label' => 'Skladem',       'count' => count($products)],
            ['label' => 'Připravujeme',  'count' => count($placeholders)],
        ]],
        ['title' => 'Cena (Kč)', 'type' => 'range', 'min' => '0 Kč', 'max' => '1 000 Kč'],
        ['title' => 'Hodnocení', 'options' => [
            ['label' => '5★ only',    'count' => 4],
            ['label' => '4★ a více',  'count' => count($products)],
            ['label' => '3★ a více',  'count' => count($products)],
        ]],
    ];

    $faqItems = [
        [
            'question' => 'Jaké odrůdy kratomu nabízíte?',
            'answer'   => '<p>V našem katalogu najdete kratomové produkty z hlavních pěstebních oblastí: Indonésie (Sumatra, Borneo), Thajsko, Vietnam. Aktuálně máme skladem 8 odrůd v různých barvách žilky — zelená, bílá, červená. Žlutá je v procesu licencování.</p>',
        ],
        [
            'question' => 'Co znamená barva žilky — zelený, bílý, červený?',
            'answer'   => '<p>Barva žilky určuje profil mitragyninu a 7-hydroxymitragyninu a způsob zpracování listu. Zelený kratom je sušený standardním způsobem, bílý je sklizen z mladších listů, červený prochází fermentací.</p>',
        ],
        [
            'question' => 'Které odrůdy jsou pro začátečníky?',
            'answer'   => '<p>Pro nové zákazníky doporučujeme začít se standardními zelenými odrůdami — např. Zelená Maeng Da nebo Zelená Sumatra. Jsou to klasické odrůdy se zaznamenanou genetikou a stálým profilem. Vždy konzultujte návod k použití na obalu.</p>',
        ],
        [
            'question' => 'Jaký je rozdíl mezi práškem a extraktem?',
            'answer'   => '<p>Prášek je sušený a mletý list — standardní forma. Extrakt je koncentrovaná tekutina (10× a vyšší koncentrace mitragyninu). Vždy začínejte nižší dávkou.</p>',
        ],
        [
            'question' => 'Jak se dostanu k laboratorním testům?',
            'answer'   => '<p>Každý produkt má svůj COA (Certificate of Analysis) ke stažení v PDF. Hledejte tlačítko „Stáhnout COA" na stránce produktu nebo navštivte naši stránku <a href="' . e(Locale::url('/laboratorni-testy')) . '">/laboratorni-testy</a>.</p>',
        ],
        [
            'question' => 'Mohu objednat bez registrace?',
            'answer'   => '<p>Ano. Při dokončení objednávky můžete pokračovat jako host nebo se zaregistrovat. Registrace má výhody — uložené adresy, historie objednávek, snadné předplatné.</p>',
        ],
    ];
@endphp

<x-layouts.app
    title="Kratom — koupit prášek a extrakt | Lab-testováno | Vivadzen"
    description="Specializovaný PML e-shop kratomu pod licencí MZ ČR. Každá šarže testovaná v akreditované laboratoři VŠCHT Praha. Doručení do 180 min v Praze."
>
    <x-ui.breadcrumbs :items="[
        ['label' => 'Domů', 'href' => Locale::url('/')],
        ['label' => 'Kratom'],
    ]" />

    <x-catalog.hero
        eyebrow="KATALOG · PML SORTIMENT"
        eyebrowAccent="grass"
        title="Kratom — prášek a extrakt"
        lead="Specializovaný sortiment licencovaného PML prodejce. Každá šarže testovaná v akreditované laboratoři VŠCHT Praha."
        primaryLabel="Procházet všechny odrůdy"
        secondaryLabel="Co je kratom?"
        :secondaryHref="Locale::url('/pruvodce')"
        :stats="$heroStats"
    />

    <div x-data="catalogFilter" x-init="recount()">
        <x-catalog.chip-row :chips="$chips" activeValue="all" />

        <x-catalog.main
            :groups="$filterGroups"
            :products="$products"
            :total="count($products)"
            :activeFilters="[]"
        />
    </div>

    <x-catalog.coming-soon :placeholders="$placeholders" />

    <x-catalog.cross-link :colors="$crossLinkColors" :strains="$crossLinkStrains" />

    <x-catalog.seo-text title="Vše o našem kratom sortimentu">
        <p class="lead">Vivadzen je licencovaný specializovaný e-shop kratomu (Mitragyna speciosa) pod režimem psychomodulačních látek (PML). Náš katalog pokrývá 8 aktivních odrůd ve více barvách žilky a třech formách — prášek, extrakt a nano.</p>

        <h3>Co najdete v našem katalogu?</h3>
        <p>Sortiment Vivadzen vznikl ve spolupráci s pěstiteli z Indonésie, Thajska a Vietnamu. Pracujeme pouze s tradičními pěstiteli a odrůdami s ověřenou genetikou. Každá šarže je nezávisle testována v <a href="{{ Locale::url('/laboratorni-testy') }}">akreditované laboratoři VŠCHT Praha</a>.</p>

        <h3>Podle barvy žilky</h3>
        <p>Barva žilky listu je hlavním botanickým ukazatelem. Nabízíme <a href="{{ Locale::url('/kratom/zeleny') }}">zelený</a>, <a href="{{ Locale::url('/kratom/bily') }}">bílý</a> a <a href="{{ Locale::url('/kratom/cerveny') }}">červený</a> kratom; <a href="{{ Locale::url('/kratom/zluty') }}">žlutý</a> připravujeme.</p>

        <h3>Podle odrůdy a původu</h3>
        <p>Najdete u nás klasické odrůdy <a href="{{ Locale::url('/kratom/maeng-da') }}">Maeng Da</a>, <a href="{{ Locale::url('/kratom/sumatra') }}">Sumatra</a>, <a href="{{ Locale::url('/kratom/thajsky') }}">thajský</a>, <a href="{{ Locale::url('/kratom/slon') }}">Slon</a> i speciální <a href="{{ Locale::url('/kratom/rurut') }}">Rurut</a>. <a href="{{ Locale::url('/kratom/borneo') }}">Borneo</a> je v přípravě.</p>

        <h3>Forma — prášek, extrakt, nano</h3>
        <p>Standardní formou je <a href="{{ Locale::url('/kratom/prasek') }}">prášek</a>. Koncentrované <a href="{{ Locale::url('/kratom/extrakt') }}">extrakty</a> obsahují 10× vyšší koncentraci mitragyninu. <a href="{{ Locale::url('/kratom/nano') }}">Nano</a> je jemně mletý prášek (≤ 50 μm).</p>

        <h3>Jak vybrat ten správný kratom?</h3>
        <p>Vybírejte podle dostupných údajů — botanického původu, šarže a profilu mitragyninu. Vždy konzultujte návod k použití na obalu a nepřekračujte doporučené denní dávky.</p>

        <h3>Naše standardy kvality</h3>
        <p>Každá šarže prochází kontrolou obsahu mitragyninu, 7-hydroxymitragyninu, mikrobiologie, čistoty a obsahu těžkých kovů (Pb, Cd, Hg, As). COA je dostupné ke stažení u každého produktu.</p>

        <h3>Doručení a osobní odběr</h3>
        <p>V Praze a Ostravě nabízíme Express doručení do 180 minut. Po celé ČR — 1–3 pracovní dny prostřednictvím <a href="{{ Locale::url('/doruceni') }}">Zásilkovny, PPL nebo České pošty</a>. Osobní odběr v Praze — zdarma.</p>

        <blockquote>
            <strong>Upozornění:</strong> Tento výrobek není potravinou. Není určen osobám mladším 18 let. Užívejte v souladu s návodem k použití. Více v sekci <a href="#faq">časté dotazy</a> nebo na stránce produktu.
        </blockquote>
    </x-catalog.seo-text>

    <section class="section section--cream-50 faq" id="faq" aria-labelledby="faq-title">
        <div class="container">
            <div class="faq__grid">
                <div>
                    <p class="t-overline section-head__eyebrow--soft">ČASTÉ DOTAZY</p>
                    <h2 id="faq-title" class="t-heading-xl t-on-light-accent mt-3">O našem katalogu</h2>
                    <p class="t-body-md t-on-light-2 mt-4">Pokud nenajdete odpověď, napište nám na podporu.</p>
                    <div class="mt-6">
                        <x-ui.button :href="Locale::url('/podpora')" variant="text" icon="arrow-right">Kontaktovat podporu</x-ui.button>
                    </div>
                </div>
                <x-ui.accordion :items="$faqItems" />
            </div>
        </div>
    </section>

    <x-sections.newsletter />

    @push('schema')
        <script type="application/ld+json">
        {
          "{{ '@' }}context": "https://schema.org",
          "{{ '@' }}type": "CollectionPage",
          "name": "Kratom — prášek a extrakt, laboratorně testovaný",
          "description": "Specializovaný PML e-shop kratomu pod licencí MZ ČR.",
          "url": "{{ url(Locale::url('/kratom')) }}",
          "breadcrumb": {
            "{{ '@' }}type": "BreadcrumbList",
            "itemListElement": [
              { "{{ '@' }}type": "ListItem", "position": 1, "name": "Domů",   "item": "{{ url(Locale::url('/')) }}" },
              { "{{ '@' }}type": "ListItem", "position": 2, "name": "Kratom", "item": "{{ url(Locale::url('/kratom')) }}" }
            ]
          },
          "mainEntity": {
            "{{ '@' }}type": "ItemList",
            "numberOfItems": {{ count($products) }},
            "itemListElement": [
              @foreach($products as $i => $p)
                {
                  "{{ '@' }}type": "ListItem",
                  "position": {{ $i + 1 }},
                  "url": "{{ url(Locale::url('/kratom/' . $p['slug'])) }}",
                  "name": @json($p['name'])
                }@if(!$loop->last),@endif
              @endforeach
            ]
          }
        }
        </script>
        <script type="application/ld+json">
        {
          "{{ '@' }}context": "https://schema.org",
          "{{ '@' }}type": "FAQPage",
          "mainEntity": [
            @foreach($faqItems as $i)
              {
                "{{ '@' }}type": "Question",
                "name": @json($i['question']),
                "acceptedAnswer": {
                  "{{ '@' }}type": "Answer",
                  "text": @json(strip_tags($i['answer']))
                }
              }@if(!$loop->last),@endif
            @endforeach
          ]
        }
        </script>
    @endpush
</x-layouts.app>
