@props([
    'product' => [],
])

<section class="section section--cream coa-section" id="laboratorni-test" aria-labelledby="coa-title">
    <div class="container container--narrow">
        <div class="coa">
            <div class="coa__head">
                <div class="coa__icon"><x-ui.icon name="flask" :size="32" /></div>
                <p class="t-overline t-on-light-accent">AKREDITOVANÁ LABORATOŘ VŠCHT PRAHA</p>
                <h2 id="coa-title" class="t-heading-xl t-on-light-accent mt-2">Laboratorní test této šarže</h2>
                <p class="coa__batch">Šarže <strong>{{ $product['batch'] }}</strong></p>
            </div>

            <table class="coa__table">
                <thead>
                    <tr>
                        <th scope="col">Parametr</th>
                        <th scope="col">Hodnota</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Obsah mitragyninu</td>
                        <td class="coa__num">{{ $product['mitragynin'] }} %</td>
                        <td><span class="coa__pass">✓ PASS</span></td>
                    </tr>
                    <tr>
                        <td>Obsah 7-hydroxymitragyninu</td>
                        <td class="coa__num">{{ $product['h7mg'] }} %</td>
                        <td><span class="coa__pass">✓ PASS</span></td>
                    </tr>
                    <tr>
                        <td>Čistota</td>
                        <td class="coa__num">{{ $product['purity'] }} %</td>
                        <td><span class="coa__pass">✓ PASS</span></td>
                    </tr>
                    <tr>
                        <td>Mikrobiologie<br><span class="coa__sub">ČSN ISO 21527</span></td>
                        <td>Vyhovuje</td>
                        <td><span class="coa__pass">✓ PASS</span></td>
                    </tr>
                    <tr>
                        <td>Těžké kovy (Pb, Cd, Hg, As)</td>
                        <td class="coa__num">&lt; 0,3 ppm</td>
                        <td><span class="coa__pass">✓ PASS</span></td>
                    </tr>
                    <tr>
                        <td>Datum testu</td>
                        <td>{{ $product['testedAt'] }}</td>
                        <td>VŠCHT Praha</td>
                    </tr>
                </tbody>
            </table>

            <p class="coa__caption">Plné COA s razítkem laboratoře.</p>

            <div class="coa__ctas">
                <x-ui.button variant="outline-light" icon="arrow-right">
                    Stáhnout COA (PDF)
                </x-ui.button>
                <x-ui.button :href="\App\Support\Locale::url('/laboratorni-testy')" variant="text" icon="arrow-right">
                    Všechny šarže
                </x-ui.button>
            </div>
        </div>
    </div>
</section>
