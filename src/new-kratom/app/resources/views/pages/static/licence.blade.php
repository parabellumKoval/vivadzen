@php use App\Support\Locale; @endphp

<x-layouts.app
    title="{{ __('site.pages.licence') }} | Vivadzen"
    description="Vivadzen je autorizovaný prodejce psychomodulačních látek (PML) podle zákona č. 167/1998 Sb. Licence MZ ČR."
>
    <x-static.hero
        icon="shield-check"
        :eyebrow="__('site.pages.licence')"
        title="Autorizovaný prodejce PML"
        lead="Vivadzen prodává kratom pod oprávněním Ministerstva zdravotnictví ČR jako psychomodulační látku (PML) dle zákona č. 167/1998 Sb."
    />

    <section class="static-section">
        <div class="container">
            <div class="info-stack">
                <article class="info-card info-card--dark">
                    <p class="info-card__eyebrow">Licence MZ ČR</p>
                    <h2>Číslo oprávnění: MZDR-XXXXX/2026</h2>
                    <p>Vivadzen s.r.o. je registrovaný subjekt v evidenci Ministerstva zdravotnictví České republiky jako autorizovaný prodejce kratomu (PML) pro koncové spotřebitele 18+.</p>
                    <p>Licence se vztahuje na prodej v kamenných prodejnách v Praze a v autorizovaném e-shopu vivadzen.com.</p>
                </article>

                <article class="info-card">
                    <p class="info-card__eyebrow">Co znamená PML?</p>
                    <h2>Psychomodulační látky</h2>
                    <p>Od 1. 1. 2025 platí v ČR nová kategorie „psychomodulační látky" (PML) — režim mezi potravinou a léčivem. Kratom je zařazen mezi PML.</p>
                    <p>Prodej PML smí provádět pouze autorizovaný prodejce s povolením MZ ČR. Maximum balení 50 g, povinná evidence šarží a věkové ověření 18+.</p>
                </article>

                <article class="info-card">
                    <p class="info-card__eyebrow">Co to znamená pro vás</p>
                    <h2>Zákonná ochrana spotřebitele</h2>
                    <ul class="info-card__list">
                        <li>Každá šarže má laboratorní COA z akreditované lab. (VŠCHT Praha).</li>
                        <li>Profesionální balení a značení v souladu s vyhláškami MZ ČR.</li>
                        <li>Povinná evidence skladu a zpětná dohledatelnost každé jednotky.</li>
                        <li>Věkové ověření 18+ na kase i při doručení kurýrem.</li>
                        <li>Profesionální dohled MZ ČR — kontroly, vzorkování, audity.</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <x-static.cta
        title="Nakupujte u autorizovaného prodejce"
        text="Vivadzen — kratom pod plnou licencí MZ ČR. Bezpečně, legálně, transparentně."
        :secondaryLabel="'Laboratorní testy'"
        :secondaryHref="Locale::url('/laboratorni-testy')"
    />
</x-layouts.app>
