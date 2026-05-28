@php use App\Support\Locale; @endphp

<x-layouts.app
    title="{{ __('site.pages.prodejny') }} | Vivadzen"
    description="Dvě kamenné prodejny v Praze. Osobní odběr zdarma. Po–Pá 10:00–19:00, So 10:00–14:00."
>
    <x-static.hero
        icon="store"
        :eyebrow="__('site.pages.prodejny')"
        title="Naše prodejny v Praze"
        lead="Dvě kamenné prodejny, odborné konzultace a osobní odběr zdarma. Připravíme objednávku obvykle do 60 minut."
    />

    <section class="static-section">
        <div class="container">
            <div class="grid-cards grid-cards--2">
                <article class="store-card">
                    <div class="store-card__photo" aria-hidden="true">
                        <span>Praha 2 · Karlovo nám.</span>
                    </div>
                    <div class="store-card__body">
                        <h3>Prodejna Praha 2 — Karlovo náměstí</h3>
                        <p><strong>Karlovo nám. 5, 120 00 Praha 2</strong></p>
                        <p>Tel.: <a href="tel:+420000000000">+420 000 000 000</a></p>

                        <dl class="store-card__hours">
                            <div><dt>Po–Pá</dt><dd>10:00 — 19:00</dd></div>
                            <div><dt>So</dt><dd>10:00 — 14:00</dd></div>
                            <div><dt>Ne</dt><dd>Zavřeno</dd></div>
                        </dl>

                        <p class="store-card__hint">Metro Karlovo náměstí, výstup k Resslově ulici, 50 m pěšky.</p>
                    </div>
                </article>

                <article class="store-card">
                    <div class="store-card__photo" aria-hidden="true">
                        <span>Praha 6 · Bělohorská</span>
                    </div>
                    <div class="store-card__body">
                        <h3>Prodejna Praha 6 — Bělohorská</h3>
                        <p><strong>Bělohorská 100, 169 00 Praha 6</strong></p>
                        <p>Tel.: <a href="tel:+420000000001">+420 000 000 001</a></p>

                        <dl class="store-card__hours">
                            <div><dt>Po–Pá</dt><dd>10:00 — 19:00</dd></div>
                            <div><dt>So</dt><dd>10:00 — 14:00</dd></div>
                            <div><dt>Ne</dt><dd>Zavřeno</dd></div>
                        </dl>

                        <p class="store-card__hint">Tramvaj Drinopol nebo Malovanka, 100 m pěšky.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="static-section static-section--alt">
        <div class="container container--narrow">
            <header class="static-section__head static-section__head--center">
                <p class="static-section__eyebrow">Osobní odběr</p>
                <h2 class="static-section__title">Jak vyzvednout objednávku</h2>
            </header>

            <ol class="lab-steps">
                <li><span class="lab-steps__num">01</span><h3>Vyberte „Osobní odběr"</h3><p>V košíku zvolte při doručení Osobní odběr — Praha a vyberte prodejnu.</p></li>
                <li><span class="lab-steps__num">02</span><h3>Dostanete e-mail</h3><p>Připravíme zboží obvykle do 60 minut. Pošleme e-mail, že je k vyzvednutí.</p></li>
                <li><span class="lab-steps__num">03</span><h3>Vyzvedněte na prodejně</h3><p>S sebou doklad totožnosti (18+) a číslo objednávky. Platba na prodejně možná hotově i kartou.</p></li>
            </ol>
        </div>
    </section>

    <x-static.cta
        title="Začněte u nás osobně"
        text="Naši kolegové vám rádi pomohou s výběrem. Můžete přijít i bez objednávky."
        :secondaryHref="Locale::url('/kontakt')"
        :secondaryLabel="__('site.pages.kontakt')"
    />
</x-layouts.app>
