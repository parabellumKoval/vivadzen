{{-- S5 Categories — 04_HOMEPAGE.md §7 (Image 3) --}}
@php
    $forma = [
        ['title' => 'Prášek',  'subtitle' => 'Klasická forma', 'href' => '/kratom/prasek',  'image' => asset('assets/categories/prasek.png')],
        ['title' => 'Extrakt', 'subtitle' => 'Tekutá forma',   'href' => '/kratom/extrakt', 'image' => asset('assets/categories/extract.png')],
    ];

    $odrudy = [
        ['title' => 'Maeng Da', 'subtitle' => 'Prémiová odrůda', 'href' => '/kratom/maeng-da', 'image' => asset('assets/categories/regions/maeng-da.png')],
        ['title' => 'Sumatra',  'subtitle' => 'Ostrovní původ',  'href' => '/kratom/sumatra',  'image' => asset('assets/categories/regions/sumatra.png')],
        ['title' => 'Thajský',  'subtitle' => 'Tradiční původ',  'href' => '/kratom/thajsky',  'image' => asset('assets/categories/regions/thai.png')],
        ['title' => 'Elephant', 'subtitle' => 'Silný profil',    'href' => '/kratom/slon',     'image' => asset('assets/categories/regions/eliph.png')],
        ['title' => 'Bali',     'subtitle' => 'Jemný profil',    'href' => '/kratom/bali',     'image' => asset('assets/categories/regions/bali.png')],
        ['title' => 'Borneo',   'subtitle' => 'Tropický původ',  'href' => '/kratom/borneo',   'image' => asset('assets/categories/regions/borneo.png')],
    ];

    $barvy = [
        ['title' => 'Zelený',  'subtitle' => 'Green strain',  'href' => '/kratom/zeleny',  'image' => asset('assets/categories/green.png')],
        ['title' => 'Bílý',    'subtitle' => 'White strain',  'href' => '/kratom/bily',    'image' => asset('assets/categories/white.png')],
        ['title' => 'Červený', 'subtitle' => 'Red strain',    'href' => '/kratom/cerveny', 'image' => asset('assets/categories/red.png')],
        ['title' => 'Žlutý',   'subtitle' => 'Yellow strain', 'href' => '/kratom/zluty',   'image' => asset('assets/categories/gold.png')],
    ];
@endphp

<section class="section section--cream cats-section" aria-labelledby="cats-title">
    <img class="cats-section__bg cats-section__bg--left"
         src="{{ asset('assets/categories/bg-left.png') }}"
         alt="" aria-hidden="true" loading="lazy" decoding="async">
    <img class="cats-section__bg cats-section__bg--right"
         src="{{ asset('assets/categories/bg-right.png') }}"
         alt="" aria-hidden="true" loading="lazy" decoding="async">

    <div class="container">
        <x-ui.section-head
            eyebrow="EXPLORE CATEGORIES"
            title="Najděte svůj druh kratomu"
            titleTag="h2"
            titleClass="t-display-md t-on-light-accent"
            lead="Specializovaný sortiment podle formy, odrůdy a barvy žilky. Každá kategorie odpovídá konkrétnímu profilu mitragyninu a způsobu zpracování."
            center
        />

        <div class="cats">
            {{-- Ряд 1: Odrůdy — на всю ширину, карточки равномерно --}}
            <div class="cats__group cats__group--odrudy">
                <p class="cats__group-label">Odrůdy</p>
                <div class="cats__cards">
                    @foreach($odrudy as $c)
                        <x-ui.category-card
                            :title="$c['title']"
                            :subtitle="$c['subtitle']"
                            :href="$c['href']"
                            :image="$c['image']"
                            media="region"
                        />
                    @endforeach
                </div>
            </div>

            {{-- Ряд 2: Forma produktu (слева) + Barva žilky (справа) --}}
            <div class="cats__row">
                <div class="cats__group cats__group--forma">
                    <p class="cats__group-label">Forma produktu</p>
                    <div class="cats__cards">
                        @foreach($forma as $c)
                            <x-ui.category-card
                                :title="$c['title']"
                                :subtitle="$c['subtitle']"
                                :href="$c['href']"
                                :image="$c['image']"
                                media="cover"
                            />
                        @endforeach
                    </div>
                </div>

                <div class="cats__group cats__group--barvy">
                    <p class="cats__group-label">Barva žilky</p>
                    <div class="cats__cards">
                        @foreach($barvy as $c)
                            <x-ui.category-card
                                :title="$c['title']"
                                :subtitle="$c['subtitle']"
                                :href="$c['href']"
                                :image="$c['image']"
                                :deco="asset('assets/categories/color-right-kratom.png')"
                                media="plate"
                            />
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-center mt-12">
            <x-ui.button href="/kratom" variant="text" icon="arrow-right">
                Zobrazit celý katalog
            </x-ui.button>
        </div>
    </div>
</section>
