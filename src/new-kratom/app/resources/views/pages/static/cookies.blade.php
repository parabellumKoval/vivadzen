@php use App\Support\Locale; @endphp

<x-layouts.app
    title="{{ __('site.pages.cookies') }} | Vivadzen"
    description="Jaké cookies používáme na vivadzen.com — technické, analytické, marketingové. Souhlasy a možnost odvolání."
>
    <x-static.hero
        icon="shield-check"
        :eyebrow="__('site.pages.cookies')"
        title="Cookies a sledování"
        lead="Používáme jen ty cookies, které jsou nezbytné nebo na které máme váš souhlas."
    />

    <section class="static-section">
        <div class="container container--narrow">
            <article class="prose">
                <h2>1. Co jsou cookies</h2>
                <p>Cookies jsou malé textové soubory ukládané ve vašem prohlížeči. Slouží k zapamatování stavu (přihlášení, košík), měření používání nebo personalizaci marketingu.</p>

                <h2>2. Typy cookies, které používáme</h2>

                <h3>Nezbytné (vždy zapnuto)</h3>
                <ul>
                    <li><code>laravel_session</code> — relace, košík, přihlášení</li>
                    <li><code>XSRF-TOKEN</code> — ochrana proti CSRF útokům</li>
                    <li><code>age_verified</code> — potvrzení věku 18+ (1 rok)</li>
                </ul>

                <h3>Analytické (volitelné)</h3>
                <ul>
                    <li><code>_ga, _ga_*</code> — Google Analytics 4, agregovaná statistika návštěvnosti</li>
                </ul>

                <h3>Marketingové (volitelné)</h3>
                <ul>
                    <li><code>_fbp</code> — Meta Pixel pro remarketing reklamy</li>
                </ul>

                <h2>3. Vaše ovládání</h2>
                <p>Souhlas se cookies můžete kdykoliv změnit v patičce stránky („Nastavení cookies"). Můžete je rovněž smazat v nastavení prohlížeče.</p>

                <h2>4. Doba uchovávání</h2>
                <p>Nezbytné cookies — do uzavření relace nebo 1 rok. Analytické — 14 měsíců. Marketingové — 90 dní.</p>

                <p class="prose__signature">Vivadzen s.r.o. · Platné od 1. 1. 2026</p>
            </article>
        </div>
    </section>
</x-layouts.app>
