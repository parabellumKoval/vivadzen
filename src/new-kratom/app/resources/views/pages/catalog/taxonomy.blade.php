{{--
    /kratom/{barva|odruda|forma} — категорийная страница
    Соответствует vivadzen-design-tz/05_STRANKA_KATALOG_KATEGORIE.md §2–§4.
    Variables: $taxonomyType ('color'|'strain'|'form'), $taxonomyKey, $taxonomy
--}}

@php
    use App\Support\Catalog;
    use App\Support\Locale;

    $allProducts   = Catalog::products();
    $placeholders  = Catalog::placeholders();
    $colors        = Catalog::colors();
    $strains       = Catalog::strains();
    $forms         = Catalog::forms();

    // Фильтрация по таксономии
    $products = collect($allProducts)->filter(fn ($p) => match ($taxonomyType) {
        'color'  => $p['color']  === $taxonomyKey,
        'strain' => $p['strain'] === $taxonomyKey,
        'form'   => $p['form']   === $taxonomyKey,
        default  => true,
    })->values()->all();

    // Hero
    $heroAccent = match ($taxonomyType) {
        'color'  => match ($taxonomyKey) {
            'zeleny'  => 'grass',
            'bily'    => 'cream',
            'cerveny' => 'terra',
            'zluty'   => 'amber',
            default   => 'grass',
        },
        'strain' => 'amber',
        'form'   => 'grass',
    };

    $taxonomyName = $taxonomy['label'];

    [$heroEyebrow, $heroTitle, $heroLead] = match ($taxonomyType) {
        'color' => [
            'PODLE BARVY ŽILKY',
            $taxonomy['h1'],
            $taxonomyKey === 'zluty'
                ? 'Žlutá odrůda kratomu prochází licencováním. Přihlaste se k notifikaci a budete první.'
                : 'Klasický profil. Standardní zpracování. Široká nabídka odrůd v této barvě žilky.',
        ],
        'strain' => [
            'ODRŮDA · ' . mb_strtoupper($taxonomyName),
            $taxonomyName . ' — odrůda kratomu',
            'Speciální odrůda Mitragyna speciosa s vlastní genetikou a profilem. ' . ($taxonomy['origin'] ?? ''),
        ],
        'form' => [
            'FORMA · ' . mb_strtoupper($taxonomyName),
            'Kratomový ' . mb_strtolower($taxonomyName),
            $taxonomyKey === 'extrakt'
                ? 'Koncentrovaná forma kratomu. Pro zkušené uživatele. Vždy dodržujte návod k použití.'
                : ($taxonomyKey === 'nano'
                    ? 'Jemně mletý list (≤ 50 μm) s vyšší rozpustností.'
                    : 'Klasická prášková forma kratomu.'),
        ],
    };

    // Stats card
    $heroStats = match ($taxonomyType) {
        'color' => [
            ['label' => 'Odrůd skladem',      'value' => count($products)],
            ['label' => 'Mitragynin profil',  'value' => $taxonomy['rangeMin'] . ' – ' . $taxonomy['rangeMax'] . ' %'],
            ['label' => 'Aktuální šarže',     'value' => count($products)],
            ['label' => 'Doporučená dávka',   'value' => $taxonomy['dose']],
        ],
        'strain' => [
            ['label' => 'Země původu',        'value' => $taxonomy['origin'] ?? 'Indonésie'],
            ['label' => 'Naše šarže',         'value' => count($products) . ' aktivní'],
            ['label' => 'Mitragynin profil',  'value' => '1,2 – 1,7 %'],
            ['label' => 'Barev v této odrůdě','value' => collect($products)->pluck('color')->unique()->count()],
        ],
        'form' => [
            ['label' => 'Odrůd skladem',      'value' => count($products)],
            ['label' => 'Mitragynin profil',  'value' => $taxonomyKey === 'extrakt' ? '~ 12 %' : '1,2 – 1,6 %'],
            ['label' => 'Balení',             'value' => $taxonomyKey === 'extrakt' ? '10 ml' : '25 g / 50 g'],
            ['label' => 'COA dostupných',     'value' => 'ANO'],
        ],
    };

    // chip-row — те же 7 chips но с активным текущим
    $activeChip = match ($taxonomyType) {
        'color', 'strain', 'form' => $taxonomyKey,
        default => 'all',
    };

    $chips = [
        ['value' => 'all',      'label' => 'Vše'],
        ['value' => 'zeleny',   'label' => 'Zelený'],
        ['value' => 'bily',     'label' => 'Bílý'],
        ['value' => 'cerveny',  'label' => 'Červený'],
        ['value' => 'zluty',    'label' => 'Žlutý'],
        ['value' => 'maeng-da', 'label' => 'Maeng Da'],
        ['value' => 'extrakt',  'label' => 'Extrakt'],
    ];

    // Filter sidebar
    $filterGroups = [
        [
            'title' => 'Barva žilky', 'open' => true,
            'options' => collect($colors)->map(fn ($c) => [
                'label' => $c['label'], 'vein' => $c['vein'],
                'count' => collect($allProducts)->where('color', $c['slug'])->count(),
                'disabled' => !empty($c['comingSoon']),
            ])->values()->all(),
        ],
        [
            'title' => 'Odrůda', 'open' => true,
            'options' => collect($strains)->map(fn ($s) => [
                'label' => $s['label'],
                'count' => collect($allProducts)->where('strain', $s['slug'])->count(),
                'disabled' => !empty($s['comingSoon']),
            ])->values()->all(),
        ],
        [
            'title' => 'Forma',
            'options' => collect($forms)->map(fn ($f) => [
                'label' => $f['label'],
                'count' => collect($allProducts)->where('form', $f['slug'])->count(),
            ])->values()->all(),
        ],
        ['title' => 'Dostupnost', 'open' => true, 'options' => [
            ['label' => 'Skladem',      'count' => count($allProducts)],
            ['label' => 'Připravujeme', 'count' => count($placeholders)],
        ]],
        ['title' => 'Cena (Kč)', 'type' => 'range', 'min' => '0 Kč', 'max' => '1 000 Kč'],
    ];

    $activeFilters = [
        ['label' => $taxonomyName, 'value' => $taxonomyKey],
    ];

    // Cross-link
    $crossLinkColors = collect($colors)->map(fn ($c) => array_merge($c, [
        'count'  => collect($allProducts)->where('color', $c['slug'])->count(),
        'href'   => Locale::url('/kratom/' . $c['slug']),
        'active' => ($taxonomyType === 'color' && $taxonomyKey === $c['slug']),
    ]))->all();

    $crossLinkStrains = collect($strains)->map(fn ($s) => array_merge($s, [
        'href'   => Locale::url('/kratom/' . $s['slug']),
        'active' => ($taxonomyType === 'strain' && $taxonomyKey === $s['slug']),
    ]))->all();

    // Breadcrumbs
    $breadcrumbs = [
        ['label' => 'Domů', 'href' => Locale::url('/')],
        ['label' => 'Kratom', 'href' => Locale::url('/kratom')],
        ['label' => $taxonomyName],
    ];

    // SEO meta
    $titlePrefix = match ($taxonomyType) {
        'color'  => $taxonomyName . ' kratom — koupit, lab-testováno',
        'strain' => $taxonomyName . ' kratom — odrůda, prášek a extrakt',
        'form'   => 'Kratom ' . mb_strtolower($taxonomyName) . ' — všechny odrůdy',
    };

    $faqItems = match ($taxonomyType) {
        'color' => [
            ['question' => 'Čím se ' . mb_strtolower($taxonomyName) . ' kratom liší od ostatních barev?', 'answer' => '<p>Barva žilky určuje profil mitragyninu a způsob zpracování. Konkrétní data k jednotlivým odrůdám najdete na stránce produktu pod laboratorním testem.</p>'],
            ['question' => 'Jak skladovat ' . mb_strtolower($taxonomyName) . ' kratom?', 'answer' => '<p>Skladujte v suchu, při pokojové teplotě, mimo dosah osob mladších 18 let. Není potřeba lednice.</p>'],
            ['question' => 'Jaký ' . mb_strtolower($taxonomyName) . ' kratom doporučujete pro začátečníky?', 'answer' => '<p>Doporučujeme klasické odrůdy s ověřenou genetikou. Konkrétní volbu vždy konzultujte s našimi prodejci.</p>'],
        ],
        'strain' => [
            ['question' => 'Z jaké země pochází ' . $taxonomyName . '?', 'answer' => '<p>' . ($taxonomy['origin'] ?? '') . '. Bližší informace v sekci „O odrůdě" výše.</p>'],
            ['question' => 'Které barvy ' . $taxonomyName . ' nabízíte?', 'answer' => '<p>Aktuálně dostupné barvy najdete v sekci produktů na této stránce. Některé odrůdy mohou být v přípravě.</p>'],
            ['question' => 'Jak rozpoznat kvalitní ' . $taxonomyName . '?', 'answer' => '<p>Hledejte produkt s COA (certifikát laboratorní analýzy), jasným označením šarže a regionu původu. Více v <a href="' . e(Locale::url('/pruvodce')) . '">průvodci kvalitou</a>.</p>'],
        ],
        'form' => [
            ['question' => 'Co je to forma „' . $taxonomyName . '"?', 'answer' => '<p>Více v sekci „O formě" výše.</p>'],
            ['question' => 'Jak ' . mb_strtolower($taxonomyName) . ' dávkovat?', 'answer' => '<p>Vždy dle návodu k použití na obalu. U <a href="' . e(Locale::url('/kratom/extrakt')) . '">extraktů</a> začínejte minimální dávkou.</p>'],
        ],
    };

    $aboutTitle = match ($taxonomyType) {
        'color'  => 'O ' . mb_strtolower($taxonomyName) . ' kratomu',
        'strain' => 'O odrůdě ' . $taxonomyName,
        'form'   => 'O formě „' . mb_strtolower($taxonomyName) . '"',
    };

    $aboutParagraphs = match ($taxonomyType) {
        'color' => [
            'Barva žilky listu Mitragyna speciosa je hlavní botanickou klasifikací. ' . $taxonomyName . ' kratom má svůj specifický profil mitragyninu (' . $taxonomy['rangeMin'] . ' – ' . $taxonomy['rangeMax'] . ' %).',
            'Tradiční zpracování listu z této kategorie probíhá v kontrolovaných sušárnách. Po sušení následuje mletí na požadovanou frakci (jemně mletý prášek, mesh ≈ 80, nebo speciální nano profil pod 50 μm).',
            'V našem sortimentu najdete ' . count($products) . ' aktivních odrůd v barvě „' . $taxonomyName . '". Každá šarže prochází laboratorní kontrolou v akreditované laboratoři VŠCHT Praha.',
        ],
        'strain' => [
            'Odrůda ' . $taxonomyName . ' patří k ověřeným botanickým variantám Mitragyna speciosa. Pochází z oblasti ' . ($taxonomy['origin'] ?? 'Indonésie') . '.',
            'List má svůj typický profil mitragyninu a 7-hydroxymitragyninu, který se mírně mění mezi šaržemi. Konkrétní hodnoty pro každou šarži najdete v COA na stránce produktu.',
            'V této odrůdě aktuálně nabízíme ' . count($products) . ' aktivních produktů v různých barvách žilky.',
        ],
        'form' => [
            $taxonomyKey === 'extrakt'
                ? 'Kratomový extrakt je koncentrovaná vodná forma získaná tradiční metodou redukce. Obsah mitragyninu dosahuje 10× a více oproti práškové formě.'
                : ($taxonomyKey === 'nano'
                    ? 'Nano kratom je jemně mletý práškový list s velikostí částic pod 50 μm. Lepší rozpustnost ve vodě.'
                    : 'Práškový kratom je klasická forma — sušený list semletý na frakci ≈ 80 mesh.'),
            'V této formě aktuálně nabízíme ' . count($products) . ' produktů.',
        ],
    };
@endphp

<x-layouts.app
    :title="$titlePrefix . ' | Vivadzen'"
    :description="$heroLead"
>
    <x-ui.breadcrumbs :items="$breadcrumbs" />

    <x-catalog.hero
        :eyebrow="$heroEyebrow"
        :eyebrowAccent="$heroAccent"
        :title="$heroTitle"
        :lead="$heroLead"
        :primaryLabel="'Procházet ' . mb_strtolower($taxonomyName) . ' odrůdy'"
        secondaryLabel="Vše o kratomu"
        :secondaryHref="Locale::url('/pruvodce')"
        :stats="$heroStats"
    />

    <x-catalog.about-block
        :title="$aboutTitle"
        :paragraphs="$aboutParagraphs"
        :imageLabel="$taxonomyName . ' — Mitragyna speciosa list'"
        :imageCaption="$taxonomyName . ' — list před zpracováním'"
    />

    @if($taxonomyType === 'form' && $taxonomyKey === 'extrakt')
        <div class="container">
            <div class="safety-callout safety-callout--terra">
                <div class="safety-callout__icon"><x-ui.icon name="zap" :size="24" /></div>
                <div>
                    <strong>EXTRAKT</strong> je koncentrovaná forma kratomu. Obsah mitragyninu je 10× a vyšší než u prášku. Vždy začínejte minimální dávkou (1–2 kapky). Konzultujte návod k použití na obalu.
                </div>
            </div>
        </div>
    @endif

    <x-catalog.chip-row :chips="$chips" :activeValue="$activeChip" />

    <x-catalog.main
        :groups="$filterGroups"
        :products="$products"
        :total="count($products)"
        :activeFilters="$activeFilters"
    />

    <x-catalog.coming-soon :placeholders="array_slice($placeholders, 0, 3)" />

    <x-catalog.cross-link
        :colors="$crossLinkColors"
        :strains="$crossLinkStrains"
        :colorsTitle="$taxonomyType === 'color' ? 'Ostatní barvy' : 'Podle barvy žilky'"
        :strainsTitle="$taxonomyType === 'strain' ? 'Ostatní odrůdy' : 'Podle odrůdy'"
    />

    <x-catalog.seo-text
        eyebrow="{{ mb_strtoupper($taxonomyName) }} · KOMPLETNÍ INFORMACE"
        :title="$aboutTitle"
    >
        @foreach($aboutParagraphs as $p)
            <p>{{ $p }}</p>
        @endforeach

        <h3>Naše standardy pro {{ mb_strtolower($taxonomyName) }}</h3>
        <p>Každá šarže prochází kontrolou v akreditované laboratoři <a href="{{ Locale::url('/laboratorni-testy') }}">VŠCHT Praha</a>. Testujeme obsah mitragyninu, 7-hydroxymitragyninu, čistotu, mikrobiologii a obsah těžkých kovů.</p>

        <h3>Související odrůdy a barvy</h3>
        <p>Procházejte další kategorie: <a href="{{ Locale::url('/kratom/zeleny') }}">zelený</a>, <a href="{{ Locale::url('/kratom/bily') }}">bílý</a>, <a href="{{ Locale::url('/kratom/cerveny') }}">červený</a>, <a href="{{ Locale::url('/kratom/maeng-da') }}">Maeng Da</a>, <a href="{{ Locale::url('/kratom/sumatra') }}">Sumatra</a>, <a href="{{ Locale::url('/kratom/extrakt') }}">extrakt</a>.</p>

        <blockquote>
            <strong>Upozornění:</strong> Tento výrobek není potravinou. Není určen osobám mladším 18 let. Užívejte v souladu s návodem k použití. Nepřekračujte doporučenou denní dávku.
        </blockquote>
    </x-catalog.seo-text>

    <section class="section section--cream-50 faq" id="faq" aria-labelledby="faq-title">
        <div class="container">
            <div class="faq__grid">
                <div>
                    <p class="t-overline section-head__eyebrow--soft">ČASTÉ DOTAZY</p>
                    <h2 id="faq-title" class="t-heading-xl t-on-light-accent mt-3">O kategorii {{ $taxonomyName }}</h2>
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
          "name": @json($titlePrefix),
          "description": @json($heroLead),
          "url": "{{ url(Locale::url('/kratom/' . $taxonomyKey)) }}",
          "breadcrumb": {
            "{{ '@' }}type": "BreadcrumbList",
            "itemListElement": [
              { "{{ '@' }}type": "ListItem", "position": 1, "name": "Domů",   "item": "{{ url(Locale::url('/')) }}" },
              { "{{ '@' }}type": "ListItem", "position": 2, "name": "Kratom", "item": "{{ url(Locale::url('/kratom')) }}" },
              { "{{ '@' }}type": "ListItem", "position": 3, "name": @json($taxonomyName), "item": "{{ url(Locale::url('/kratom/' . $taxonomyKey)) }}" }
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

        @if($taxonomyType === 'strain')
            <script type="application/ld+json">
            {
              "{{ '@' }}context": "https://schema.org",
              "{{ '@' }}type": "Article",
              "headline": @json($heroTitle),
              "author": { "{{ '@' }}type": "Person", "name": "Garant Vivadzen", "url": "{{ url(Locale::url('/o-nas')) }}" },
              "publisher": {
                "{{ '@' }}type": "Organization",
                "name": "Vivadzen",
                "logo": { "{{ '@' }}type": "ImageObject", "url": "{{ asset('assets/brand/logo-vivadzen-primary-light.avif') }}" }
              },
              "datePublished": "2026-01-15",
              "dateModified": @json(now()->toIso8601String()),
              "mainEntityOfPage": "{{ url(Locale::url('/kratom/' . $taxonomyKey)) }}"
            }
            </script>
        @endif

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
