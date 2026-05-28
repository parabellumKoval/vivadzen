@php use App\Support\Locale; @endphp

<x-layouts.app
    title="{{ __('site.pages.reklamace') }} | Vivadzen"
    description="14 dní na vrácení bez udání důvodu. Postup vrácení a reklamace zboží od Vivadzen."
>
    <x-static.hero
        icon="shield-check"
        :eyebrow="__('site.pages.reklamace')"
        title="Vrácení a reklamace"
        lead="Máte 14 dní na vrácení bez udání důvodu. Reklamace vyřizujeme rychle, transparentně a vždy k vaší spokojenosti."
    />

    <section class="static-section">
        <div class="container">
            <div class="grid-cards grid-cards--2">
                <article class="info-card">
                    <p class="info-card__eyebrow">14 dní na vrácení</p>
                    <h2>Bez udání důvodu</h2>
                    <p>Pokud vám zboží nevyhovuje, máte ze zákona 14 dní od převzetí na vrácení. Plný refund na účet do 14 dní od přijetí vrácené zásilky.</p>
                    <ul class="info-card__list">
                        <li>Zboží musí být nepoškozené a v původním obalu</li>
                        <li>Hygienicky uzavřené pakování musí zůstat neporušené</li>
                        <li>Náklady na zaslání zpět hradí kupující</li>
                    </ul>
                </article>

                <article class="info-card info-card--dark">
                    <p class="info-card__eyebrow">Reklamace</p>
                    <h2>Vada zboží do 24 měsíců</h2>
                    <p>Pokud na zboží objevíte vadu (vzhled, balení, vůně, neshoda s COA), máte právo na reklamaci do 24 měsíců.</p>
                    <ul class="info-card__list">
                        <li>Vyplňte reklamační formulář a popište závadu</li>
                        <li>Posoudíme do 30 dnů (zákonná lhůta)</li>
                        <li>Při uznané reklamaci — výměna, sleva nebo vrácení peněz</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <section class="static-section static-section--alt">
        <div class="container container--narrow">
            <header class="static-section__head static-section__head--center">
                <p class="static-section__eyebrow">Postup</p>
                <h2 class="static-section__title">Jak na vrácení nebo reklamaci</h2>
            </header>

            <ol class="lab-steps">
                <li><span class="lab-steps__num">01</span><h3>Kontaktujte nás</h3><p>Napište na <a href="mailto:reklamace@vivadzen.com">reklamace@vivadzen.com</a> nebo vyplňte formulář v účtu.</p></li>
                <li><span class="lab-steps__num">02</span><h3>Pošlete zboží</h3><p>Spolu s objednacím číslem na adresu prodejny Praha 2 — Karlovo nám. 5.</p></li>
                <li><span class="lab-steps__num">03</span><h3>Vyřízení do 14–30 dní</h3><p>U vrácení (14 dní) — peníze do 14 dní. U reklamace (vada) — do 30 dní vyjádření.</p></li>
            </ol>
        </div>
    </section>

    <x-static.cta
        title="Potřebujete poradit s vrácením?"
        text="Kontaktujte naši podporu — odpovídáme do 4 hodin v pracovní době."
        :primaryHref="Locale::url('/kontakt')"
        :primaryLabel="__('site.pages.kontakt')"
        :secondaryHref="Locale::url('/obchodni-podminky')"
        :secondaryLabel="__('site.pages.podmínky')"
    />
</x-layouts.app>
