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
                <div class="footer__badges">
                    <x-ui.badge variant="lab" icon="flask">Akreditovaná laboratoř</x-ui.badge>
                    <x-ui.badge variant="licence" icon="shield-check">Autorizovaný prodejce PML</x-ui.badge>
                    <x-ui.badge variant="age">18+</x-ui.badge>
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
                <span class="pay-logo">VISA</span>
                <span class="pay-logo">MC</span>
                <span class="pay-logo">APAY</span>
                <span class="pay-logo">GPAY</span>
                <span class="pay-logo">QR</span>
            </div>
        </div>
    </div>
</footer>
