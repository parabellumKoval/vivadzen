{{--
    Внутренняя страница-каталог UI-кита.
    Показывает все компоненты в одном месте — для дизайн-ревью и QA.
    URL: /styleguide
--}}
<x-layouts.app
    :announcement="false"
    title="Styleguide · Vivadzen UI Kit"
    description="Каталог UI-компонентов и токенов."
>
    <section class="section section--cream">
        <div class="container flex flex-col gap-16">

            <header>
                <p class="t-overline t-on-light-2">UI KIT</p>
                <h1 class="t-display-md t-on-light-accent mt-3">Vivadzen Design System</h1>
                <p class="t-body-lg t-on-light-2 mt-4" style="max-width:64ch">
                    Все базовые компоненты и токены. См. <code>docs/COMPONENTS.md</code>
                    и <code>docs/STYLEGUIDE.md</code> для полной документации.
                </p>
            </header>

            {{-- ============ COLORS ============ --}}
            <section>
                <h2 class="t-heading-lg t-on-light-accent">Палитра</h2>
                <div class="mt-6" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:var(--sp-3);">
                    @php
                        $swatches = [
                            ['forest-700','#1B3A2D','Brand primary'],
                            ['forest-900','#0F2419','Hover/pressed'],
                            ['cream-100','#F9F4EC','Surface light'],
                            ['cream-200','#F5EDD8','Surface warm'],
                            ['grass-500','#7EC855','Accent green'],
                            ['amber-500','#F4A020','CTA primary'],
                            ['terracotta-500','#D45C2B','Urgency/sale'],
                            ['ink-900','#14201B','Text dark'],
                            ['ink-500','#5C6A63','Text muted'],
                        ];
                    @endphp
                    @foreach($swatches as [$name,$hex,$role])
                        <div class="card card--bordered" style="padding:0; overflow:hidden;">
                            <div style="height:80px; background: {{ $hex }};"></div>
                            <div style="padding:var(--sp-3);">
                                <div style="font-weight:600; font-size:13px;">{{ $name }}</div>
                                <div style="font-size:12px; color:var(--c-ink-500);">{{ $hex }}</div>
                                <div style="font-size:11px; color:var(--c-ink-500); margin-top:2px;">{{ $role }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ============ TYPOGRAPHY ============ --}}
            <section>
                <h2 class="t-heading-lg t-on-light-accent">Типографика</h2>
                <div class="mt-6 flex flex-col gap-4">
                    <div class="t-display-xl t-on-light-accent">Display XL — Playfair</div>
                    <div class="t-display-lg t-on-light-accent">Display LG</div>
                    <div class="t-display-md t-on-light-accent">Display MD (italic)</div>
                    <div class="t-heading-xl t-on-light-accent">Heading XL</div>
                    <div class="t-heading-lg t-on-light-accent">Heading LG</div>
                    <div class="t-heading-md">Heading MD (Inter)</div>
                    <div class="t-heading-sm">Heading SM</div>
                    <div class="t-body-lg">Body LG — Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
                    <div class="t-body-md">Body MD — Lorem ipsum dolor sit amet.</div>
                    <div class="t-body-sm t-on-light-2">Body SM — secondary text.</div>
                    <div class="t-overline t-terra">Overline / Eyebrow</div>
                </div>
            </section>

            {{-- ============ BUTTONS ============ --}}
            <section>
                <h2 class="t-heading-lg t-on-light-accent">Кнопки</h2>
                <div class="mt-6 flex flex-wrap gap-3 items-center">
                    <x-ui.button variant="primary" icon="arrow-right">Primary</x-ui.button>
                    <x-ui.button variant="outline-light">Outline light</x-ui.button>
                    <x-ui.button variant="solid-dark">Solid dark</x-ui.button>
                    <x-ui.button variant="grass">Grass</x-ui.button>
                    <x-ui.button variant="terracotta">Terracotta</x-ui.button>
                    <x-ui.button variant="ghost">Ghost</x-ui.button>
                    <x-ui.button variant="text" icon="arrow-right">Text link</x-ui.button>
                    <x-ui.button variant="primary" disabled>Disabled</x-ui.button>
                </div>

                <div class="mt-6" style="background: var(--c-forest-700); padding: var(--sp-6); border-radius: var(--r-md);">
                    <div class="flex flex-wrap gap-3 items-center">
                        <x-ui.button variant="primary" icon="arrow-right">Primary on dark</x-ui.button>
                        <x-ui.button variant="secondary">Secondary on dark</x-ui.button>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3 items-center">
                    <x-ui.button variant="primary" size="sm">Small</x-ui.button>
                    <x-ui.button variant="primary" size="md">Medium</x-ui.button>
                    <x-ui.button variant="primary" size="lg">Large</x-ui.button>
                </div>
            </section>

            {{-- ============ BADGES ============ --}}
            <section>
                <h2 class="t-heading-lg t-on-light-accent">Badges</h2>
                <div class="mt-6 flex flex-wrap gap-3" style="background: var(--c-forest-800); padding: var(--sp-6); border-radius: var(--r-md);">
                    <x-ui.badge variant="age">18+</x-ui.badge>
                    <x-ui.badge variant="lab" icon="flask">Akreditovaná lab.</x-ui.badge>
                    <x-ui.badge variant="licence" icon="shield-check">Licence PML</x-ui.badge>
                    <x-ui.badge variant="sale">−15 %</x-ui.badge>
                    <x-ui.badge variant="out">Vyprodáno</x-ui.badge>
                    <x-ui.badge variant="subscription">Předplatné</x-ui.badge>
                    <x-ui.badge variant="express">EXPRESS 180</x-ui.badge>
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <x-ui.badge variant="tag">PRŮVODCE</x-ui.badge>
                    <x-ui.badge variant="tag-amber">LEGISLATIVA</x-ui.badge>
                    <x-ui.badge variant="tag-terra">KVALITA</x-ui.badge>
                </div>

                <h3 class="t-heading-md mt-6">Vein dots</h3>
                <div class="mt-3 flex flex-wrap gap-4 items-center">
                    <span><span class="vein-dot vein-dot--red"></span> red</span>
                    <span><span class="vein-dot vein-dot--green"></span> green</span>
                    <span><span class="vein-dot vein-dot--white"></span> white</span>
                    <span><span class="vein-dot vein-dot--yellow"></span> yellow</span>
                    <span><span class="vein-dot vein-dot--mix"></span> mix</span>
                </div>
            </section>

            {{-- ============ INPUTS ============ --}}
            <section>
                <h2 class="t-heading-lg t-on-light-accent">Формы</h2>
                <div class="mt-6" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:var(--sp-6); max-width:720px;">
                    <x-ui.input label="Email" name="sg-email" placeholder="vy@example.cz" required />
                    <x-ui.input label="PSČ" name="sg-psc" placeholder="120 00" helper="Pro výpočet dopravy" />
                    <x-ui.input label="Telefon" name="sg-phone" placeholder="+420 ..." error="Neplatné číslo." />
                </div>
            </section>

            {{-- ============ CARDS ============ --}}
            <section>
                <h2 class="t-heading-lg t-on-light-accent">Карточки товаров</h2>
                <div class="mt-6" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:var(--sp-6);">
                    <x-ui.product-card
                        name="Červená Maeng Da"
                        strainLabel="Červená žilka · Borneo"
                        vein="red"
                        mitragynin="1,42 % · jemně mletý"
                        :price25="290" :price50="520"
                        rating="4.9" reviewsCount="142 hodnocení"
                        :badge="['variant' => 'sale', 'label' => '−10 %']"
                    />
                    <x-ui.product-card
                        name="Zelená Sumatra"
                        strainLabel="Zelená žilka · Sumatra"
                        vein="green"
                        mitragynin="1,38 % · jemně mletý"
                        :price25="270" :price50="490"
                        rating="4.8" reviewsCount="98 hodnocení"
                    />
                </div>
            </section>

            <section>
                <h2 class="t-heading-lg t-on-light-accent">Карточки категорий</h2>
                <div class="mt-6 cats__grid">
                    <x-ui.category-card title="Zelený" subtitle="Zelená žilka" href="#" glyph="green" />
                    <x-ui.category-card title="Bílý" subtitle="Bílá žilka" href="#" glyph="white" />
                    <x-ui.category-card title="Červený" subtitle="Červená žilka" href="#" glyph="red" />
                    <x-ui.category-card title="Žlutý" subtitle="Žlutá žilka" href="#" glyph="yellow" />
                    <x-ui.category-card title="Maeng Da" subtitle="Klasická" href="#" glyph="forest" icon="sparkles" />
                    <x-ui.category-card title="Extrakt" subtitle="Tekutá" href="#" glyph="amber" icon="flask" />
                    <x-ui.category-card title="Předplatné" subtitle="−10 %" href="#" glyph="terra" icon="badge-check" />
                </div>
            </section>

            {{-- ============ REVIEW ============ --}}
            <section>
                <h2 class="t-heading-lg t-on-light-accent">Карточки отзывов (dark)</h2>
                <div class="mt-6" style="background:var(--c-forest-700); padding:var(--sp-8); border-radius:var(--r-lg);">
                    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:var(--sp-6);">
                        <x-ui.review-card
                            quote="Profesionální balení, dodání druhý den. Šarže má COA."
                            name="Martin K." date="březen 2026" chip="Maeng Da"
                        />
                        <x-ui.review-card
                            quote="Konečně český obchod, který publikuje lab-výsledky."
                            name="Tereza N." date="únor 2026" chip="Bílý slon"
                        />
                    </div>
                </div>
            </section>

            {{-- ============ ACCORDION ============ --}}
            <section>
                <h2 class="t-heading-lg t-on-light-accent">Аккордеон</h2>
                <div class="mt-6" style="max-width:640px;">
                    <x-ui.accordion :items="[
                        ['question' => 'Co je kratom?', 'answer' => '<p>Kratom (Mitragyna speciosa) je rostlina pocházející z jihovýchodní Asie.</p>'],
                        ['question' => 'Jak jej Vivadzen testuje?', 'answer' => '<p>Každá šarže prochází nezávislým testem v laboratoři VŠCHT Praha.</p>'],
                    ]" />
                </div>
            </section>

            {{-- ============ PLACEHOLDERS ============ --}}
            <section>
                <h2 class="t-heading-lg t-on-light-accent">Заглушки ассетов</h2>
                <p class="t-body-md t-on-light-2 mt-3" style="max-width:64ch">
                    Используются везде, где будут сгенерированные/студийные фото и SVG.
                    Подсказка <code>hint</code> содержит ожидаемый путь к ассету.
                </p>
                <div class="mt-6" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:var(--sp-6);">
                    <x-ui.placeholder shape="square" label="Product photo 1:1" hint="/assets/products/{slug}-square.avif" icon="leaf" />
                    <x-ui.placeholder shape="portrait" variant="dark" label="Hero portrait 4:5" hint="/assets/ai-generated/hero/home-hero.avif" />
                    <x-ui.placeholder shape="wide" label="Article 16:9" hint="/assets/ai-generated/guides/...avif" />
                </div>
            </section>

        </div>
    </section>
</x-layouts.app>
