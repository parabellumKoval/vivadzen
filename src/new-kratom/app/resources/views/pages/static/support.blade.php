@php use App\Support\Locale; @endphp

<x-layouts.app
    title="Podpora a časté dotazy | Vivadzen"
    description="Časté dotazy o kratomu, doručení, platbách a vrácení. Kontakt na podporu Vivadzen."
>
    <x-static.hero
        icon="badge-check"
        eyebrow="Podpora"
        title="Pomůžeme. Jednoduše."
        lead="Najděte odpovědi v častých dotazech nebo nás kontaktujte přímo. Odpovídáme do 4 h v pracovní době."
    />

    <section class="static-section">
        <div class="container container--narrow">
            <header class="static-section__head">
                <p class="static-section__eyebrow">FAQ</p>
                <h2 class="static-section__title">Časté dotazy</h2>
            </header>

            <div class="faq">
                <details class="faq__item" open>
                    <summary>Jaký kratom je pro začátečníka?</summary>
                    <p>Pro začátek doporučujeme zelené odrůdy (Zelená Maeng Da nebo Zelená Sumatra) v balení 25 g. Vždy začínejte s nízkou dávkou (1–2 g) a sledujte reakci.</p>
                </details>
                <details class="faq__item">
                    <summary>Co znamená hodnota mitragyninu na obalu?</summary>
                    <p>Mitragynin je hlavní alkaloid kratomu. Hodnota 1,2–1,5 % je standard u kvalitní šarže. Vyšší ≠ lepší — je to o vyrovnaném profilu.</p>
                </details>
                <details class="faq__item">
                    <summary>Mohu kombinovat kratom s léky nebo alkoholem?</summary>
                    <p>Nedoporučujeme. Kratom může mít interakce s léky (zejména opiáty, antidepresiva). Konzultujte s lékařem. Nikdy nekombinujte s alkoholem.</p>
                </details>
                <details class="faq__item">
                    <summary>Jak dlouho kratom skladovat?</summary>
                    <p>Skladujte v suchu a chladu, mimo dosah dětí. Při správném skladování si zachová profil 12–18 měsíců. Datum minimální trvanlivosti je na obalu.</p>
                </details>
                <details class="faq__item">
                    <summary>Mohu vrátit otevřené balení?</summary>
                    <p>Bohužel ne. Hygienicky balené produkty s porušeným ochranným uzávěrem nelze vrátit. Pokud máte problém s kvalitou, postupujte přes reklamaci.</p>
                </details>
                <details class="faq__item">
                    <summary>Doručujete anonymně?</summary>
                    <p>Balení je v neutrálních krabicích bez nápisu „Kratom". Adresa odesílatele uvádí pouze „Vivadzen s.r.o.", bez zmínky o obsahu.</p>
                </details>
            </div>

            <div class="support-contact">
                <h3>Nenašli jste odpověď?</h3>
                <p>Napište nám — odpovídáme rychle a věcně.</p>
                <a href="{{ Locale::url('/kontakt') }}" class="btn btn--primary btn--lg">
                    Napsat podpoře
                    <span class="btn__icon"><x-ui.icon name="arrow-right" /></span>
                </a>
            </div>
        </div>
    </section>
</x-layouts.app>
