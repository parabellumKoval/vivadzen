@php use App\Support\Locale; @endphp

<x-layouts.app
    title="{{ __('site.pages.lab') }} | Vivadzen"
    description="Každá šarže kratomu testovaná v akreditované laboratoři VŠCHT Praha dle ISO 17025. Plné COA ke stažení u každého produktu."
>
    <x-static.hero
        icon="flask"
        :eyebrow="__('site.pages.lab')"
        title="Lab testy ke každé šarži"
        lead="Akreditovaná laboratoř VŠCHT Praha podle ISO 17025. Mitragynin, 7-OH-mitragynin, mikrobiologie a těžké kovy — testujeme každou šarži než jde do prodeje."
    />

    <section class="static-section">
        <div class="container">
            <div class="lab-grid">
                <div class="lab-spec">
                    <p class="lab-spec__eyebrow">Standard akreditace</p>
                    <h2 class="lab-spec__title">ISO/IEC 17025 — VŠCHT Praha</h2>
                    <p>Vysoká škola chemicko-technologická v Praze je nejvyšší českou autoritou pro chemické analýzy přírodních látek. Laboratoř má akreditaci podle ISO/IEC 17025, což je nejvyšší stupeň důvěry pro výsledky laboratorních testů.</p>

                    <ul class="lab-spec__list">
                        <li><strong>Mitragynin</strong> — hlavní alkaloid, profil 0,8–1,8 %</li>
                        <li><strong>7-hydroxymitragynin</strong> — sekundární alkaloid, max. 0,02 %</li>
                        <li><strong>Mikrobiologie</strong> — ČSN ISO 21527</li>
                        <li><strong>Těžké kovy</strong> — Pb, Cd, Hg, As (≤ 0,3 ppm)</li>
                        <li><strong>Vlhkost a čistota</strong> — &lt; 8 % vlhkost</li>
                    </ul>
                </div>

                <aside class="lab-coa">
                    <p class="lab-coa__eyebrow">Vzor COA</p>
                    <div class="lab-coa__sample">
                        <div class="lab-coa__row"><span>Šarže</span><strong>VD-2026-014</strong></div>
                        <div class="lab-coa__row"><span>Datum testu</span><strong>12. 03. 2026</strong></div>
                        <div class="lab-coa__divider"></div>
                        <div class="lab-coa__row"><span>Mitragynin</span><strong>1,42 %</strong><span class="lab-coa__status">PASS</span></div>
                        <div class="lab-coa__row"><span>7-OH-mitragynin</span><strong>0,008 %</strong><span class="lab-coa__status">PASS</span></div>
                        <div class="lab-coa__row"><span>Čistota</span><strong>99,1 %</strong><span class="lab-coa__status">PASS</span></div>
                        <div class="lab-coa__row"><span>Mikrobiologie</span><strong>Vyhovuje</strong><span class="lab-coa__status">PASS</span></div>
                        <div class="lab-coa__row"><span>Těžké kovy</span><strong>&lt; 0,3 ppm</strong><span class="lab-coa__status">PASS</span></div>
                    </div>
                    <p class="lab-coa__note">U každého produktu si můžete stáhnout plné COA s razítkem laboratoře.</p>
                </aside>
            </div>
        </div>
    </section>

    <section class="static-section static-section--alt">
        <div class="container">
            <header class="static-section__head static-section__head--center">
                <p class="static-section__eyebrow">Jak to děláme</p>
                <h2 class="static-section__title">Cesta každé šarže k vám</h2>
            </header>

            <ol class="lab-steps">
                <li>
                    <span class="lab-steps__num">01</span>
                    <h3>Příjem suroviny</h3>
                    <p>Šarže od ověřených dodavatelů z Indonésie/Thajska. Vstupní kontrola obalu a vzorku.</p>
                </li>
                <li>
                    <span class="lab-steps__num">02</span>
                    <h3>Odběr vzorku do laboratoře</h3>
                    <p>Reprezentativní vzorek (≥ 100 g) zasíláme do VŠCHT Praha s identifikací šarže.</p>
                </li>
                <li>
                    <span class="lab-steps__num">03</span>
                    <h3>Analýza dle ISO 17025</h3>
                    <p>HPLC stanovení obsahu alkaloidů, mikrobiologie, kovy. Trvá 5–10 pracovních dnů.</p>
                </li>
                <li>
                    <span class="lab-steps__num">04</span>
                    <h3>Vystavení COA</h3>
                    <p>Pokud šarže projde, vydáme COA. Skenujeme a vystavujeme online u každého produktu.</p>
                </li>
                <li>
                    <span class="lab-steps__num">05</span>
                    <h3>Balíme a expedujeme</h3>
                    <p>Šarže jde do prodeje jen po pozitivním COA. Při neshodě se vrací dodavateli.</p>
                </li>
            </ol>
        </div>
    </section>

    <section class="static-section">
        <div class="container container--narrow">
            <header class="static-section__head static-section__head--center">
                <h2 class="static-section__title">Co je COA a jak ho číst?</h2>
            </header>
            <div class="faq">
                <details class="faq__item" open>
                    <summary>Co znamená „mitragynin 1,42 %"?</summary>
                    <p>Mitragynin je hlavní alkaloid kratomu. 1,42 % znamená, že v 1 gramu prášku je 14,2 mg mitragyninu. Tato hodnota je deklarována pro každou šarži.</p>
                </details>
                <details class="faq__item">
                    <summary>Co je 7-hydroxymitragynin?</summary>
                    <p>Sekundární alkaloid kratomu. V přírodní šarži má být velmi nízká hodnota (do 0,02 %). Vyšší hodnoty mohou indikovat oxidaci nebo neautenticitu produktu.</p>
                </details>
                <details class="faq__item">
                    <summary>Co znamená status PASS / FAIL?</summary>
                    <p>PASS znamená, že parametr odpovídá specifikaci. Pokud by byl FAIL, šarži bychom neuvedli do prodeje.</p>
                </details>
                <details class="faq__item">
                    <summary>Mohu si COA stáhnout?</summary>
                    <p>Ano. Plný PDF COA s razítkem VŠCHT je dostupný u každého produktu v záložce „Laboratorní test".</p>
                </details>
            </div>
        </div>
    </section>

    <x-static.cta
        title="Vyberte si laboratorně ověřenou šarži"
        text="Každý produkt na Vivadzen má za sebou ověřený laboratorní test."
        :primaryLabel="'Prohlédnout kratom'"
        :secondaryLabel="'Licence MZ ČR'"
        :secondaryHref="Locale::url('/licence')"
    />
</x-layouts.app>
