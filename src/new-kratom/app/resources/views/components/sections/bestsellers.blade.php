{{-- S6 Bestsellers — 04_HOMEPAGE.md §8 --}}
@php
    $products = [
        [
            'name' => 'Červená Maeng Da',
            'strainLabel' => 'Červená žilka · Borneo',
            'vein' => 'red',
            'mitragynin' => '1,42 % · jemně mletý',
            'price25' => 290, 'price50' => 520,
            'rating' => '4.9', 'reviewsCount' => '142 hodnocení',
            'badge' => ['variant' => 'sale', 'label' => '−10 %'],
            'href' => '/produkt/cervena-maeng-da',
            'image' => asset('assets/products/cervena-maeng-da/01-front.png'),
        ],
        [
            'name' => 'Zelená Sumatra',
            'strainLabel' => 'Zelená žilka · Sumatra',
            'vein' => 'green',
            'mitragynin' => '1,38 % · jemně mletý',
            'price25' => 270, 'price50' => 490,
            'rating' => '4.8', 'reviewsCount' => '98 hodnocení',
            'badge' => null,
            'href' => '/produkt/zelena-sumatra',
            'image' => asset('assets/products/zelena-sumatra/01-front.png'),
        ],
        [
            'name' => 'Bílá Maeng Da',
            'strainLabel' => 'Bílá žilka · Borneo',
            'vein' => 'white',
            'mitragynin' => '1,55 % · jemně mletý',
            'price25' => 310, 'price50' => 560,
            'rating' => '4.9', 'reviewsCount' => '186 hodnocení',
            'badge' => ['variant' => 'subscription', 'label' => 'Předplatné'],
            'href' => '/produkt/bila-maeng-da',
            'image' => asset('assets/products/bila-maeng-da/01-front.png'),
        ],
        [
            'name' => 'Kratom Extrakt 10 ml',
            'strainLabel' => 'Tekutý extrakt · Zelený',
            'vein' => 'green',
            'mitragynin' => '12 % · vodný extrakt',
            'price25' => 0, 'price50' => 0,
            'rating' => '4.7', 'reviewsCount' => '74 hodnocení',
            'badge' => ['variant' => 'express', 'label' => 'EXPRESS 180'],
            'href' => '/produkt/extrakt-10ml',
            'image' => asset('assets/products/kratom-extrakt-zeleny-10ml/101-front.png'),
        ],
        [
            'name' => 'Zelená Maeng Da',
            'strainLabel' => 'Zelená žilka · Borneo',
            'vein' => 'green',
            'mitragynin' => '1,48 % · jemně mletý',
            'price25' => 290, 'price50' => 520,
            'rating' => '4.9', 'reviewsCount' => '124 hodnocení',
            'badge' => null,
            'href' => '/produkt/zelena-maeng-da',
            'image' => asset('assets/products/zelena-maeng-da/01-front.png'),
        ],
        [
            'name' => 'Bílý Slon',
            'strainLabel' => 'Bílá žilka · Indonésie',
            'vein' => 'white',
            'mitragynin' => '1,50 % · jemně mletý',
            'price25' => 300, 'price50' => 540,
            'rating' => '4.8', 'reviewsCount' => '112 hodnocení',
            'badge' => null,
            'href' => '/produkt/bily-slon',
            'image' => asset('assets/products/bily-slon/01-front.png'),
        ],
        [
            'name' => 'Zelený Thajský',
            'strainLabel' => 'Zelená žilka · Thajsko',
            'vein' => 'green',
            'mitragynin' => '1,36 % · jemně mletý',
            'price25' => 280, 'price50' => 500,
            'rating' => '4.7', 'reviewsCount' => '86 hodnocení',
            'badge' => null,
            'href' => '/produkt/zeleny-thajsky',
            'image' => asset('assets/products/zeleny-thajsky/01-front.png'),
        ],
        [
            'name' => 'Zelený Rurut Nano',
            'strainLabel' => 'Zelená žilka · Borneo',
            'vein' => 'green',
            'mitragynin' => '1,52 % · nano mletý',
            'price25' => 320, 'price50' => 580,
            'rating' => '4.9', 'reviewsCount' => '64 hodnocení',
            'badge' => ['variant' => 'subscription', 'label' => 'Předplatné'],
            'href' => '/produkt/zeleny-rurut-nano',
            'image' => asset('assets/products/zeleny-rurut-nano/01-front.png'),
        ],
    ];
@endphp

<section class="section section--cream-50" aria-labelledby="bestsellers-title">
    <div class="container">
        <header class="bestsellers__head">
            <div>
                <p class="t-overline section-head__eyebrow--soft">AKTUÁLNĚ SKLADEM</p>
                <h2 id="bestsellers-title" class="t-heading-xl t-on-light-accent mt-2">Naše bestsellery</h2>
            </div>
            <x-ui.button href="/kratom?sort=popular" variant="text" icon="arrow-right">
                Zobrazit vše
            </x-ui.button>
        </header>

        <x-ui.carousel class="bestsellers__carousel" :desktop="3" :tablet="2" :mobile="1">
            @foreach($products as $p)
                <x-ui.product-card
                    :name="$p['name']"
                    :strainLabel="$p['strainLabel']"
                    :vein="$p['vein']"
                    :mitragynin="$p['mitragynin']"
                    :price25="$p['price25']"
                    :price50="$p['price50']"
                    :rating="$p['rating']"
                    :reviewsCount="$p['reviewsCount']"
                    :badge="$p['badge']"
                    :href="$p['href']"
                    :image="$p['image']"
                />
            @endforeach
        </x-ui.carousel>
    </div>
</section>
