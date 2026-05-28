@php use App\Support\Locale; @endphp

<x-layouts.app
    title="Průvodce kratomem | Vivadzen"
    description="Stručný průvodce kratomem — barvy, odrůdy, formy. Jak číst COA. Bezpečné používání."
>
    <x-static.hero
        icon="leaf"
        eyebrow="Průvodce"
        title="Vše, co o kratomu potřebujete vědět"
        lead="Stručně a věcně: barvy, odrůdy, formy, dávkování a jak číst laboratorní COA. Bez marketingové bídy."
    />

    <section class="static-section">
        <div class="container">
            <div class="grid-cards grid-cards--3">
                <a class="guide-card" href="{{ Locale::url('/kratom/zeleny') }}">
                    <span class="guide-card__dot guide-card__dot--green"></span>
                    <h3>Zelený kratom</h3>
                    <p>Vyrovnaný profil mitragyninu 1,2–1,6 %. Univerzální typ pro každý den.</p>
                    <span class="guide-card__arrow">→</span>
                </a>
                <a class="guide-card" href="{{ Locale::url('/kratom/bily') }}">
                    <span class="guide-card__dot guide-card__dot--white"></span>
                    <h3>Bílý kratom</h3>
                    <p>Mladý list, vyšší mitragynin 1,4–1,7 %. Specifický světlý profil.</p>
                    <span class="guide-card__arrow">→</span>
                </a>
                <a class="guide-card" href="{{ Locale::url('/kratom/cerveny') }}">
                    <span class="guide-card__dot guide-card__dot--red"></span>
                    <h3>Červený kratom</h3>
                    <p>Krátká fermentace, klasický profil 1,3–1,6 %. Tradiční volba.</p>
                    <span class="guide-card__arrow">→</span>
                </a>
            </div>
        </div>
    </section>

    <section class="static-section static-section--alt">
        <div class="container container--narrow">
            <header class="static-section__head static-section__head--center">
                <p class="static-section__eyebrow">Bezpečnost</p>
                <h2 class="static-section__title">Důležité informace před prvním nákupem</h2>
            </header>

            <article class="prose">
                <p><strong>Kratom je psychomodulační látka (PML)</strong> dle zák. č. 167/1998 Sb. Není to potravina, doplněk stravy ani léčivo. Není určen osobám mladším 18 let.</p>

                <h3>Doporučená dávka</h3>
                <ul>
                    <li>Začátečník: 1–2 g</li>
                    <li>Standardní dávka: 3–5 g</li>
                    <li>Maximální denní dávka: 10 g</li>
                </ul>

                <h3>Kdy NEUŽÍVAT</h3>
                <ul>
                    <li>Před a během řízení nebo obsluhy strojů</li>
                    <li>V těhotenství a při kojení</li>
                    <li>S léky (zejména opiáty, antidepresiva)</li>
                    <li>S alkoholem</li>
                    <li>Při onemocnění jater nebo ledvin</li>
                </ul>

                <p>Pokud si nejste jisti, konzultujte s lékařem. Při jakýchkoli nežádoucích reakcích přestaňte užívat a vyhledejte lékařskou pomoc.</p>
            </article>
        </div>
    </section>

    <x-static.cta
        title="Začněte s naším laboratorně testovaným kratomem"
        :secondaryHref="Locale::url('/laboratorni-testy')"
        :secondaryLabel="__('site.pages.lab')"
    />
</x-layouts.app>
