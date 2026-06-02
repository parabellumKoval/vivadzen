@php
    $sortiment = [
        ['label' => 'Všechny odrůdy', 'href' => '/kratom'],
        ['label' => 'Zelený kratom', 'href' => '/kratom/zeleny'],
        ['label' => 'Bílý kratom',   'href' => '/kratom/bily'],
        ['label' => 'Červený kratom','href' => '/kratom/cerveny'],
        ['label' => 'Maeng Da',      'href' => '/kratom/maeng-da'],
        ['label' => 'Extrakt 10 ml', 'href' => '/kratom/extrakt'],
        ['label' => 'Předplatné',    'href' => '/predplatne'],
    ];

    $help = [
        ['label' => 'Doručení a platba',   'href' => '/doruceni'],
        ['label' => 'Laboratorní testy',   'href' => '/laboratorni-testy'],
        ['label' => 'Licence MZ ČR',       'href' => '/licence'],
        ['label' => 'Prodejny v Praze',    'href' => '/prodejny'],
        ['label' => 'Ověření věku 18+',    'href' => '/overeni-veku'],
        ['label' => 'Reklamace a vrácení', 'href' => '/reklamace'],
        ['label' => 'Časté dotazy',        'href' => '/podpora'],
        ['label' => 'Kontakt',             'href' => '/kontakt'],
    ];
@endphp

<footer class="footer" role="contentinfo">
    <div class="container">
        <div class="footer__grid">
            <div>
                <div class="footer__brand-tag">
                    <img
                        class="footer__brand-logo"
                        src="{{ asset('assets/brand/logo-vivadzen-primary-light.avif') }}"
                        alt="Vivadzen"
                        loading="lazy"
                    />
                </div>
                <p class="footer__about">
                    Licencovaný specializovaný e-shop kratomu pod režimem PML.
                    Každá šarže testovaná v akreditované laboratoři VŠCHT Praha.
                    Dvě kamenné prodejny v Praze.
                </p>
                <div class="footer__trust" aria-label="Garance kvality">
                    <a class="footer__trust-mark" href="/laboratorni-testy" title="Akreditovaná laboratoř VŠCHT Praha">
                        <img src="{{ asset('assets/trust/vscht.jpeg') }}" alt="Akreditovaná laboratoř VŠCHT Praha" width="56" height="56" loading="lazy" />
                        <span>Akreditovaná<br>laboratoř</span>
                    </a>
                    <a class="footer__trust-mark" href="/licence" title="Autorizovaný prodejce PML — MZ ČR">
                        <img src="{{ asset('assets/trust/mzcz.png') }}" alt="MZ ČR — Autorizovaný prodejce PML" width="56" height="56" loading="lazy" />
                        <span>Autorizovaný<br>prodejce PML</span>
                    </a>
                    <span class="footer__trust-age" aria-label="Pouze pro osoby 18+">18+</span>
                </div>
            </div>

            <div>
                <p class="footer__col-title">Sortiment</p>
                <nav class="footer__links" aria-label="Sortiment">
                    @foreach($sortiment as $i)
                        <a href="{{ $i['href'] }}">{{ $i['label'] }}</a>
                    @endforeach
                </nav>
            </div>

            <div>
                <p class="footer__col-title">Pomoc & info</p>
                <nav class="footer__links" aria-label="Pomoc">
                    @foreach($help as $i)
                        <a href="{{ $i['href'] }}">{{ $i['label'] }}</a>
                    @endforeach
                </nav>
            </div>

            <div>
                <p class="footer__col-title">Novinky a šarže</p>
                <p class="footer__about">1× měsíčně. Žádný spam.</p>
                <form class="form-inline" x-data="newsletterForm" @submit.prevent="submit()">
                    <x-ui.input
                        type="email"
                        name="email"
                        placeholder="vase.email@example.cz"
                        :onDark="true"
                        :pill="true"
                        x-model="email"
                    />
                    <x-ui.button variant="primary" type="submit" size="md">Přihlásit</x-ui.button>
                </form>
                <div class="footer__social">
                    <a href="#" aria-label="Facebook"><x-ui.icon name="badge-check" /></a>
                    <a href="#" aria-label="Instagram"><x-ui.icon name="sparkles" /></a>
                    <a href="#" aria-label="Mapa prodejen"><x-ui.icon name="map-pin" /></a>
                </div>
            </div>
        </div>

        <div class="footer__bottom">
            <span>© {{ date('Y') }} Vivadzen s.r.o. · IČO 00000000 · DIČ CZ00000000</span>
            <div class="footer__pay" aria-label="Platební metody">
                <span class="pay-logo"><img src="{{ asset('assets/payment/visa.png') }}" alt="Visa" loading="lazy" /></span>
                <span class="pay-logo"><img src="{{ asset('assets/payment/mastercard.png') }}" alt="Mastercard" loading="lazy" /></span>
                <span class="pay-logo"><img src="{{ asset('assets/payment/apple-pay.png') }}" alt="Apple Pay" loading="lazy" /></span>
                <span class="pay-logo"><img src="{{ asset('assets/payment/google-pay.png') }}" alt="Google Pay" loading="lazy" /></span>
                <span class="pay-logo pay-logo--qr" aria-label="QR platba">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <path d="M14 14h3v3h-3zM20 14v3M14 20h7M17 17v3"/>
                    </svg>
                </span>
            </div>
        </div>
    </div>
</footer>
