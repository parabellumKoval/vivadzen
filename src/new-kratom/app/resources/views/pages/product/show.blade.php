{{--
    /kratom/{slug} — товарная страница (Product Detail Page)
    Соответствует vivadzen-design-tz/06_STRANKA_PRODUKT.md.
--}}

@php
    use App\Support\Catalog;
    use App\Support\Locale;

    $colors  = Catalog::colors();
    $strains = Catalog::strains();
    $related = Catalog::related($product['slug'], 4);

    $colorLabel  = $colors[$product['color']]['label']  ?? $product['colorLabel'];
    $strainLabel = $strains[$product['strain']]['label'] ?? $product['strainLabel'];

    $breadcrumbs = [
        ['label' => 'Domů',   'href' => Locale::url('/')],
        ['label' => 'Kratom', 'href' => Locale::url('/kratom')],
        ['label' => $colorLabel . ' kratom', 'href' => Locale::url('/kratom/' . $product['color'])],
        ['label' => $product['name']],
    ];

    $tabs = [
        ['id' => 'popis',                'label' => 'Popis'],
        ['id' => 'laboratorni-test',     'label' => 'Laboratorní test'],
        ['id' => 'navod',                'label' => 'Návod'],
        ['id' => 'bezpecnost',           'label' => 'Bezpečnostní informace'],
        ['id' => 'recenze',              'label' => 'Recenze', 'count' => $product['reviewsCount']],
        ['id' => 'otazky',               'label' => 'Otázky',  'count' => $product['questionsCount']],
    ];

    $title = $product['name'] . ' 25/50 g — laboratorně testováno | Vivadzen';
    $desc  = $product['name'] . ' — kratom prášek od licencovaného e-shopu. Balení 25 g a 50 g, šarže ' . $product['batch'] . ' testovaná VŠCHT Praha (mitragynin ' . $product['mitragynin'] . ' %). Doručení po ČR, osobní odběr Praha. 18+.';
@endphp

<x-layouts.app :title="$title" :description="$desc" :announcement="false">
    <x-ui.breadcrumbs :items="$breadcrumbs" />

    <section class="product-hero" aria-labelledby="product-title">
        <div class="container product-hero__grid">
            <div class="product-hero__gallery">
                <x-product.gallery
                    :name="$product['name']"
                    :gallery="$product['gallery']"
                    :batch="$product['batch']"
                    :inStock="$product['inStock']"
                />
            </div>

            <div class="product-hero__buybox">
                <x-product.buy-box :product="$product" />
            </div>
        </div>
    </section>

    <x-product.tabs :tabs="$tabs" />

    <x-product.popis    :product="$product" />
    <x-product.coa      :product="$product" />
    <x-product.navod />
    <x-product.safety />
    <x-product.delivery />
    <x-product.reviews  :product="$product" />
    <x-product.qa       :product="$product" />

    @if(!empty($related))
        <x-product.related :products="$related" />
    @endif

    <section class="section--dark product-footer-cta">
        <div class="container">
            <div class="product-footer-cta__inner">
                <h2 class="t-heading-lg t-on-dark">Máte otázku? Napište nám — odpovídáme do 24 hodin.</h2>
                <div class="product-footer-cta__ctas">
                    <x-ui.button variant="primary" :href="Locale::url('/podpora')" icon="arrow-right">Podpora</x-ui.button>
                    <x-ui.button variant="secondary" href="#faq">Časté dotazy</x-ui.button>
                </div>
            </div>
        </div>
    </section>

    @push('schema')
        <script type="application/ld+json">
        {
          "{{ '@' }}context": "https://schema.org",
          "{{ '@' }}type": "Product",
          "name": @json($product['name']),
          "image": [ "{{ asset(ltrim($product['image'], '/')) }}" ],
          "description": @json($product['description']),
          "sku": @json($product['batch']),
          "brand": { "{{ '@' }}type": "Brand", "name": "Vivadzen" },
          "category": @json('Kratom > ' . $colorLabel . ' > ' . $strainLabel),
          "offers": [
            @if(!empty($product['price25']))
              {
                "{{ '@' }}type": "Offer",
                "url": "{{ url(Locale::url('/kratom/' . $product['slug'])) }}",
                "priceCurrency": "CZK",
                "price": "{{ $product['price25'] }}",
                "itemCondition": "https://schema.org/NewCondition",
                "availability": "{{ $product['inStock'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
                "name": "25 {{ $product['unit'] ?? 'g' }}"
              }@if(!empty($product['price50'])),@endif
            @endif
            @if(!empty($product['price50']))
              {
                "{{ '@' }}type": "Offer",
                "url": "{{ url(Locale::url('/kratom/' . $product['slug'])) }}",
                "priceCurrency": "CZK",
                "price": "{{ $product['price50'] }}",
                "itemCondition": "https://schema.org/NewCondition",
                "availability": "{{ $product['inStock'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
                "name": "50 {{ $product['unit'] ?? 'g' }}"
              }
            @endif
          ],
          "aggregateRating": {
            "{{ '@' }}type": "AggregateRating",
            "ratingValue": "{{ $product['rating'] }}",
            "reviewCount": "{{ $product['reviewsCount'] }}",
            "bestRating": "5"
          }
        }
        </script>

        <script type="application/ld+json">
        {
          "{{ '@' }}context": "https://schema.org",
          "{{ '@' }}type": "BreadcrumbList",
          "itemListElement": [
            @foreach($breadcrumbs as $i => $b)
              {
                "{{ '@' }}type": "ListItem",
                "position": {{ $i + 1 }},
                "name": @json($b['label'])
                @if(!empty($b['href'])), "item": "{{ url($b['href']) }}" @endif
              }@if(!$loop->last),@endif
            @endforeach
          ]
        }
        </script>
    @endpush
</x-layouts.app>
