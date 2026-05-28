@php use App\Support\Locale; @endphp

<x-layouts.app
    title="{{ __('site.nav.predplatne') }} | Vivadzen"
    description="Předplatné kratomu od Vivadzen — sleva 10 %, přednostní zásoby, vlastní COA reporty. Zrušíte kdykoli."
>
    <x-static.hero
        icon="sparkles"
        :eyebrow="__('site.nav.predplatne')"
        title="Předplatné s benefity"
        lead="Pravidelné dodání každých 14, 30 nebo 60 dní · sleva 10 % · přednostní zásoby. Žádné závazky — zrušíte kdykoli."
    />

    <section class="static-section">
        <div class="container">
            <div class="grid-cards grid-cards--3">
                <article class="info-card">
                    <p class="info-card__eyebrow">Sleva</p>
                    <h3>−10 % vždy</h3>
                    <p>Automaticky uplatněná sleva u každého dodání. Bez kódů, bez háčků.</p>
                </article>
                <article class="info-card info-card--dark">
                    <p class="info-card__eyebrow">Přednost</p>
                    <h3>Přednostní zásoby</h3>
                    <p>Předplatitelé dostávají nové šarže jako první — než jdou do volného prodeje.</p>
                </article>
                <article class="info-card">
                    <p class="info-card__eyebrow">Transparentnost</p>
                    <h3>Osobní COA</h3>
                    <p>Každé dodání obsahuje COA šarže, kterou jste dostali. Plná dohledatelnost.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="static-section static-section--alt">
        <div class="container container--narrow">
            <header class="static-section__head static-section__head--center">
                <p class="static-section__eyebrow">Jak to funguje</p>
                <h2 class="static-section__title">Tři kroky a hotovo</h2>
            </header>

            <ol class="lab-steps">
                <li><span class="lab-steps__num">01</span><h3>Vyberte produkt a balení</h3><p>Na stránce produktu klikněte „Předplatné" a zvolte frekvenci 14 / 30 / 60 dní.</p></li>
                <li><span class="lab-steps__num">02</span><h3>Dokončete objednávku</h3><p>Standardní pokladna, uložená karta. Sleva 10 % se přičte automaticky.</p></li>
                <li><span class="lab-steps__num">03</span><h3>Spravujte v účtu</h3><p>Kdykoliv můžete posunout dodání, změnit interval nebo zrušit.</p></li>
            </ol>
        </div>
    </section>

    <x-static.cta
        title="Začněte své předplatné"
        text="Najděte si svou oblíbenou odrůdu a uložte si pravidelné dodání."
    />
</x-layouts.app>
