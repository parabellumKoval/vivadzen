{{-- S8+S9 «Reviews Showcase» — верхняя proof-сцена + светлая карусель отзывов. --}}
@php
    use App\Support\Locale;

    $reviews = \App\Support\Reviews::featured(9);

    if (empty($reviews)) {
        $reviews = [
            [
                'author' => 'Pavla N.',
                'rating' => 5,
                'date' => 'před 4 dny',
                'body' => 'Velmi rychlé doručení, balení odpovídá popisu. Šarže s vysokým obsahem mitragyninu — přesně to, co hledám. Profesionální přístup.',
                'product' => [
                    'name' => 'Červená Maeng Da',
                    'url' => Locale::url('/kratom/cervena-maeng-da'),
                    'image' => asset('assets/products/cervena-maeng-da/01-front.png'),
                    'price' => '290',
                ],
            ],
            [
                'author' => 'Filip D.',
                'rating' => 5,
                'date' => 'před 2 dny',
                'body' => 'Bílá Maeng Da je svěží a čistá. Jemné mletí, žádné hrudky. Top obchod.',
                'product' => [
                    'name' => 'Bílá Maeng Da',
                    'url' => Locale::url('/kratom/bila-maeng-da'),
                    'image' => asset('assets/products/bila-maeng-da/01-front.png'),
                    'price' => '310',
                ],
            ],
            [
                'author' => 'Martin K.',
                'rating' => 5,
                'date' => 'před 1 týdnem',
                'body' => 'Stabilní kvalita, jemné mletí. Lab-test data jsou v souladu s tím, co je deklarováno na obalu.',
                'product' => [
                    'name' => 'Červená Maeng Da',
                    'url' => Locale::url('/kratom/cervena-maeng-da'),
                    'image' => asset('assets/products/cervena-maeng-da/01-front.png'),
                    'price' => '290',
                ],
            ],
            [
                'author' => 'Anna S.',
                'rating' => 5,
                'date' => 'před 11 dny',
                'body' => 'Objednávka dorazila rychle, vše zabaleno diskrétně. Lab-testy publikované otevřeně.',
                'product' => [
                    'name' => 'Bílá Maeng Da',
                    'url' => Locale::url('/kratom/bila-maeng-da'),
                    'image' => asset('assets/products/bila-maeng-da/01-front.png'),
                    'price' => '310',
                ],
            ],
            [
                'author' => 'Ondřej P.',
                'rating' => 5,
                'date' => 'před 4 dny',
                'body' => 'Příjemná zelená odrůda, vyvážený profil. Balení precizní, doručení druhý den.',
                'product' => [
                    'name' => 'Zelená Maeng Da',
                    'url' => Locale::url('/kratom/zelena-maeng-da'),
                    'image' => asset('assets/products/zelena-maeng-da/01-front.png'),
                    'price' => '290',
                ],
            ],
            [
                'author' => 'Lucie K.',
                'rating' => 4,
                'date' => 'před 17 dny',
                'body' => 'Kvalita odpovídá ceně, COA dostupné online. Vrátím se pro větší balení.',
                'product' => [
                    'name' => 'Zelená Maeng Da',
                    'url' => Locale::url('/kratom/zelena-maeng-da'),
                    'image' => asset('assets/products/zelena-maeng-da/01-front.png'),
                    'price' => '290',
                ],
            ],
            [
                'author' => 'Petr M.',
                'rating' => 5,
                'date' => 'před 5 dny',
                'body' => 'Sumatra mě překvapila kvalitou. Konzistentní šarže, férový přístup.',
                'product' => [
                    'name' => 'Zelená Sumatra',
                    'url' => Locale::url('/kratom/zelena-sumatra'),
                    'image' => asset('assets/products/zelena-sumatra/01-front.png'),
                    'price' => '280',
                ],
            ],
            [
                'author' => 'Marek J.',
                'rating' => 5,
                'date' => 'před 6 dny',
                'body' => 'Bílý slon má skvělý poměr cena/kvalita. Transparentní COA, rychlé doručení.',
                'product' => [
                    'name' => 'Bílý Slon',
                    'url' => Locale::url('/kratom/bily-slon'),
                    'image' => asset('assets/products/bily-slon/01-front.png'),
                    'price' => '300',
                ],
            ],
            [
                'author' => 'Daniel R.',
                'rating' => 5,
                'date' => 'před 8 dny',
                'body' => 'Extrakt je čistý a přesně popsaný. Profesionální balení, COA v balíčku.',
                'product' => [
                    'name' => 'Kratom extrakt zelený',
                    'url' => Locale::url('/kratom/kratom-extrakt-10ml-zeleny'),
                    'image' => asset('assets/products/kratom-extrakt-zeleny-10ml/101-front.png'),
                    'price' => '390',
                ],
            ],
        ];
    }
@endphp

<section class="reviews-showcase" aria-labelledby="reviews-showcase-title" style="--reviews-forest: url('{{ asset('assets/reviews/forest.png') }}')">
    <div class="reviews-showcase__hero">
        <div class="reviews-showcase__hero-inner">
            <div class="reviews-showcase__scene" aria-hidden="true">
                <img class="reviews-showcase__man"
                     src="{{ asset('assets/reviews/man-drink-kratom.png') }}"
                     alt="" loading="lazy" decoding="async" />

                <img class="reviews-showcase__branch reviews-showcase__branch--fore"
                     src="{{ asset('assets/reviews/branch-left-below-overlapping-defocus.png') }}"
                     alt="" loading="lazy" decoding="async" />

                <span class="reviews-showcase__badge">
                    <svg viewBox="0 0 120 120" aria-hidden="true">
                        <defs>
                            <path id="reviews-badge-top" d="M22 60a38 38 0 0 1 76 0" />
                            <path id="reviews-badge-bottom" d="M98 60a38 38 0 0 1-76 0" />
                        </defs>
                        <circle class="reviews-showcase__badge-outer" cx="60" cy="60" r="56" />
                        <circle class="reviews-showcase__badge-inner" cx="60" cy="60" r="44" />
                        <text class="reviews-showcase__badge-copy">
                            <textPath href="#reviews-badge-top" startOffset="50%" text-anchor="middle">Příroda</textPath>
                        </text>
                        <text class="reviews-showcase__badge-copy">
                            <textPath href="#reviews-badge-bottom" startOffset="50%" text-anchor="middle">Rovnováze</textPath>
                        </text>
                        <g class="reviews-showcase__badge-leaf" fill="none">
                            <path d="M60 78V52"/>
                            <path d="M60 66c-8 0-14-5.4-14-14 8 0 14 5.4 14 14z"/>
                            <path d="M60 66c8 0 14-5.4 14-14-8 0-14 5.4-14 14z"/>
                            <path d="M60 52c-5.6 0-9.8-4-9.8-9.8 5.6 0 9.8 4 9.8 9.8z"/>
                            <path d="M60 52c5.6 0 9.8-4 9.8-9.8-5.6 0-9.8 4-9.8 9.8z"/>
                        </g>
                    </svg>
                </span>
            </div>

            <div class="reviews-showcase__content">
                <div class="reviews-showcase__mark" aria-hidden="true">
                    <span></span>
                    <svg viewBox="0 0 44 34" fill="none">
                        <path d="M22 32V12"/>
                        <path d="M22 23c-6 0-10-4-10-10 6 0 10 4 10 10z"/>
                        <path d="M22 23c6 0 10-4 10-10-6 0-10 4-10 10z"/>
                        <path d="M22 12c-4 0-7-2.8-7-7 4 0 7 2.8 7 7z"/>
                        <path d="M22 12c4 0 7-2.8 7-7-4 0-7 2.8-7 7z"/>
                    </svg>
                    <span></span>
                </div>

                <p class="reviews-showcase__eyebrow">Real people, real results</p>

                <h2 id="reviews-showcase-title" class="reviews-showcase__title">
                    <span class="reviews-showcase__title-line">Co říkají</span>
                    <span class="reviews-showcase__title-line reviews-showcase__title-line--accent">naši zákazníci</span>
                </h2>

                <div class="reviews-showcase__rating">
                    <x-ui.stars :rating="5" :size="20" class="reviews-showcase__stars stars--on-dark" />
                    <strong class="reviews-showcase__score">4,9 z 5</strong>
                    <span class="reviews-showcase__dot" aria-hidden="true"></span>
                    <span class="reviews-showcase__count">2 500+ hodnocení</span>
                </div>

                <a class="reviews-showcase__google" href="#" rel="nofollow">
                    <span class="reviews-showcase__google-g" aria-hidden="true">
                        <svg viewBox="0 0 48 48" width="25" height="25">
                            <path fill="#4285F4" d="M45.12 24.5c0-1.56-.14-3.06-.4-4.5H24v8.51h11.84c-.51 2.75-2.06 5.08-4.39 6.64v5.52h7.11c4.16-3.83 6.56-9.47 6.56-16.17z"/>
                            <path fill="#34A853" d="M24 46c5.94 0 10.92-1.97 14.56-5.33l-7.11-5.52c-1.97 1.32-4.49 2.1-7.45 2.1-5.73 0-10.58-3.87-12.31-9.07H4.34v5.7C7.96 41.07 15.4 46 24 46z"/>
                            <path fill="#FBBC05" d="M11.69 28.18C11.25 26.86 11 25.45 11 24s.25-2.86.69-4.18v-5.7H4.34C2.85 17.09 2 20.45 2 24c0 3.55.85 6.91 2.34 9.88l7.35-5.7z"/>
                            <path fill="#EA4335" d="M24 10.75c3.23 0 6.13 1.11 8.41 3.29l6.31-6.31C34.91 4.18 29.93 2 24 2 15.4 2 7.96 6.93 4.34 14.12l7.35 5.7c1.73-5.2 6.58-9.07 12.31-9.07z"/>
                        </svg>
                    </span>
                    <span class="reviews-showcase__google-text">Ověřené recenze na <strong>Google</strong></span>
                    <span class="reviews-showcase__google-check" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path d="M12 2.5 14.7 4l3.1-.1 1.4 2.8 2.4 2-.7 3 .7 3-2.4 2-1.4 2.8-3.1-.1L12 21.5l-2.7-1.6-3.1.1-1.4-2.8-2.4-2 .7-3-.7-3 2.4-2 1.4-2.8 3.1.1L12 2.5z" fill="currentColor"/>
                            <path d="m8.2 12.1 2.4 2.4 5.2-5.6" fill="none" stroke="var(--c-forest-900)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </a>

                <ul class="reviews-showcase__trust" aria-label="Důvody důvěry">
                    <li>
                        <svg viewBox="0 0 34 34" fill="none" aria-hidden="true">
                            <path d="M17 3.4 27 8v7.7c0 6.7-4.2 11.5-10 14.7C11.2 27.2 7 22.4 7 15.7V8l10-4.6z"/>
                            <path d="M17 24V11"/>
                            <path d="M17 18.4c-4.1 0-7-2.8-7-7 4.1 0 7 2.8 7 7z"/>
                            <path d="M17 18.4c4.1 0 7-2.8 7-7-4.1 0-7 2.8-7 7z"/>
                        </svg>
                        <span>Transparentní původ</span>
                    </li>
                    <li>
                        <svg viewBox="0 0 34 34" fill="none" aria-hidden="true">
                            <path d="M13 4h8"/>
                            <path d="M15 4v8.2L8.7 26a2.2 2.2 0 0 0 2 3h12.6a2.2 2.2 0 0 0 2-3L19 12.2V4"/>
                            <path d="M11.7 23h10.6"/>
                            <path d="M15 19h4"/>
                        </svg>
                        <span>Laboratorně testováno</span>
                    </li>
                    <li>
                        <svg viewBox="0 0 34 34" fill="none" aria-hidden="true">
                            <path d="M7 27C7 16 12.8 8.4 27 6c-1.9 13.7-9.3 20.2-20 21z"/>
                            <path d="M7 27c5.4-7.2 11.5-11.9 18.5-17"/>
                            <path d="M14 12c.6 4.1 3.2 6.6 7.5 7.3"/>
                        </svg>
                        <span>Prémiová kvalita</span>
                    </li>
                </ul>
            </div>
        </div>

        <svg class="reviews-showcase__wave" viewBox="0 0 1440 104" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0 40C160 70 318 9 505 34c213 29 401 28 594-1 145-21 232-19 341 11v60H0V40z" fill="currentColor"/>
        </svg>
    </div>

    <div class="reviews-showcase__reviews" x-data="reviewsSlider()">
        <div class="reviews-showcase__reviews-head">
            <span class="reviews-showcase__reviews-line" aria-hidden="true"></span>
            <svg viewBox="0 0 36 22" fill="none" aria-hidden="true">
                <path d="M18 20V9"/>
                <path d="M18 15c-4.6 0-8-3-8-8 4.6 0 8 3 8 8z"/>
                <path d="M18 15c4.6 0 8-3 8-8-4.6 0-8 3-8 8z"/>
            </svg>
            <span class="reviews-showcase__reviews-line" aria-hidden="true"></span>
        </div>

        <p class="reviews-showcase__reviews-kicker">Reference, které mluví za nás</p>

        <div class="reviews-showcase__carousel">
            <button type="button" class="reviews-showcase__nav-btn reviews-showcase__nav-btn--prev"
                    @click="prev()" aria-label="Předchozí recenze">
                <x-ui.icon name="chevron-down" :size="24" />
            </button>

            <div class="reviews-showcase__track" x-ref="track">
                @foreach($reviews as $r)
                    <x-ui.testimonial-card
                        :review="$r"
                        x-bind:class="cardClass({{ $loop->index }})"
                        x-bind:style="cardStyle({{ $loop->index }})"
                        x-bind:aria-hidden="!isActive({{ $loop->index }})"
                        x-bind:inert="!isActive({{ $loop->index }})"
                        x-bind:data-slot="slotFor({{ $loop->index }})"
                    />
                @endforeach
            </div>

            <button type="button" class="reviews-showcase__nav-btn reviews-showcase__nav-btn--next"
                    @click="next()" aria-label="Další recenze">
                <x-ui.icon name="chevron-down" :size="24" />
            </button>
        </div>

        <div class="reviews-showcase__dots" aria-label="Stránky recenzí">
            <template x-for="page in pageTotal" :key="page">
                <button type="button"
                        class="reviews-showcase__dot-btn"
                        :class="{ 'is-active': pageIndex === page - 1 }"
                        @click="goTo(page - 1)"
                        :aria-label="`Přejít na skupinu recenzí ${page}`"></button>
            </template>
        </div>

        <x-ui.button class="reviews-showcase__cta" :href="Locale::url('/recenze')" variant="solid-dark" size="lg" icon="arrow-right">
            Zobrazit všechny recenze
        </x-ui.button>
    </div>
</section>
