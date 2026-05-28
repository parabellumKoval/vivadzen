@php use App\Support\Locale; @endphp

<x-layouts.app
    title="{{ __('site.pages.doruceni') }} | Vivadzen"
    description="Možnosti doručení a platby pro kratom: kurýr po ČR, Express 180 minut v Praze a Ostravě, osobní odběr."
>
    <x-static.hero
        icon="truck"
        :eyebrow="__('site.pages.doruceni')"
        title="Doručení a platba"
        lead="Doručujeme po celé ČR. V Praze a Ostravě i do 180 minut. Vždy s ověřením věku 18+ při převzetí."
    />

    <section class="static-section">
        <div class="container">
            <div class="grid-cards grid-cards--3">
                <article class="card-feat">
                    <div class="card-feat__icon"><x-ui.icon name="truck" :size="28" /></div>
                    <h3>Doručení po ČR — kurýr</h3>
                    <p class="card-feat__price">89 Kč <small>(zdarma od 1 200 Kč)</small></p>
                    <p>Standardní kurýrní doručení po celé České republice. Doručíme do 1–3 pracovních dnů.</p>
                    <ul class="card-feat__list">
                        <li>Sledování zásilky e-mailem</li>
                        <li>Ověření věku 18+ při převzetí</li>
                        <li>Doručení Po–Pá 9:00–18:00</li>
                    </ul>
                </article>

                <article class="card-feat card-feat--accent">
                    <div class="card-feat__icon"><x-ui.icon name="zap" :size="28" /></div>
                    <span class="card-feat__badge">EXPRESS</span>
                    <h3>Express 180 minut</h3>
                    <p class="card-feat__price">290 Kč</p>
                    <p>Praha a Ostrava — doručíme přímo do rukou do 3 hodin od objednávky. Dostupné v pracovní dny.</p>
                    <ul class="card-feat__list">
                        <li>Po–Pá 10:00–18:00, So 10:00–14:00</li>
                        <li>Speciální kurýr přímo k vám</li>
                        <li>Stálý kontakt s kurýrem</li>
                    </ul>
                </article>

                <article class="card-feat">
                    <div class="card-feat__icon"><x-ui.icon name="store" :size="28" /></div>
                    <h3>Osobní odběr — Praha</h3>
                    <p class="card-feat__price">Zdarma</p>
                    <p>Vyzvedněte si zboží na jedné ze dvou prodejen v Praze. Obvykle připraveno do 60 minut po objednávce.</p>
                    <ul class="card-feat__list">
                        <li>Praha 2 — Karlovo náměstí</li>
                        <li>Praha 6 — Bělohorská</li>
                        <li>Po–Pá 10:00–19:00, So 10:00–14:00</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <section class="static-section static-section--alt">
        <div class="container">
            <header class="static-section__head">
                <p class="static-section__eyebrow">Způsoby platby</p>
                <h2 class="static-section__title">Bezpečné platby online i offline</h2>
                <p class="static-section__lead">Všechny online platby zabezpečené přes 3D Secure 2. Čísla karet neukládáme — pouze tokeny platebních partnerů.</p>
            </header>

            <div class="grid-cards grid-cards--4">
                <article class="payment-card">
                    <div class="payment-card__logos">
                        <span>Visa</span><span>Mastercard</span><span>Apple Pay</span><span>Google Pay</span>
                    </div>
                    <h3>Platba kartou online</h3>
                    <p>Okamžitá platba kartou nebo digitální peněženkou. 3D Secure ochrana.</p>
                </article>
                <article class="payment-card">
                    <div class="payment-card__logos"><span>QR</span></div>
                    <h3>QR platba</h3>
                    <p>Naskenujte QR kód v aplikaci vaší banky a potvrďte platbu.</p>
                </article>
                <article class="payment-card">
                    <div class="payment-card__logos"><span>SEPA</span></div>
                    <h3>Bankovní převod</h3>
                    <p>Reálné platební údaje pošleme e-mailem. Objednávku odešleme po připsání.</p>
                </article>
                <article class="payment-card">
                    <div class="payment-card__logos"><span>Cash</span></div>
                    <h3>Dobírka</h3>
                    <p>Platba kurýrovi při doručení hotovostí nebo kartou. Příplatek 39 Kč.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="static-section">
        <div class="container container--narrow">
            <header class="static-section__head static-section__head--center">
                <p class="static-section__eyebrow">Časté dotazy</p>
                <h2 class="static-section__title">Vše, co potřebujete vědět</h2>
            </header>

            <div class="faq">
                <details class="faq__item" open>
                    <summary>Jak probíhá ověření věku?</summary>
                    <p>Při převzetí zásilky kurýr požádá o doklad totožnosti. Bez doložení 18+ věku nebude zásilka předána a vrátí se nám.</p>
                </details>
                <details class="faq__item">
                    <summary>Doručujete i do Slovenska?</summary>
                    <p>V první fázi doručujeme pouze po Česku. Mezinárodní expedice plánujeme zavést postupně podle regulačního prostředí.</p>
                </details>
                <details class="faq__item">
                    <summary>Kdy bude zásilka odeslána?</summary>
                    <p>Objednávky přijaté do 14:00 v pracovní den expedujeme týž den. Pro Express 180 min — okamžitě.</p>
                </details>
                <details class="faq__item">
                    <summary>Mohu změnit adresu po odeslání?</summary>
                    <p>Adresu lze změnit do okamžiku předání kurýrovi. Napište nám neprodleně na <a href="{{ Locale::url('/kontakt') }}">kontakt</a>.</p>
                </details>
            </div>
        </div>
    </section>

    <x-static.cta />
</x-layouts.app>
