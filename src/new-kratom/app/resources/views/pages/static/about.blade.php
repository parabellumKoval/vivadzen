@php use App\Support\Locale; @endphp

<x-layouts.app
    title="{{ __('site.pages.o_nas') }} | Vivadzen"
    description="Vivadzen je český licencovaný prodejce kratomu. Laboratorně testovaný, transparentní, dospělý přístup k psychomodulačním látkám."
>
    <x-static.hero
        icon="leaf"
        :eyebrow="__('site.pages.o_nas')"
        title="Herbal apothecary s evropskou disciplínou"
        lead="Vivadzen vznikl z přesvědčení, že psychomodulační látky si zaslouží serióznost — laboratoř, licence, dospělý přístup. Žádné zázračné sliby, jen kvalita a transparence."
    />

    <section class="static-section">
        <div class="container">
            <div class="about-grid">
                <article class="about-card">
                    <span class="about-card__num">01</span>
                    <h3>Transparence</h3>
                    <p>Každá šarže má vlastní COA z VŠCHT. Zveřejňujeme všechny výsledky včetně negativních — a takové šarže neprodáváme.</p>
                </article>
                <article class="about-card">
                    <span class="about-card__num">02</span>
                    <h3>Licence</h3>
                    <p>Pracujeme pod přímým dohledem MZ ČR jako autorizovaný prodejce PML. Audity, kontroly, evidence šarží.</p>
                </article>
                <article class="about-card">
                    <span class="about-card__num">03</span>
                    <h3>Dospělý přístup</h3>
                    <p>Nepíšeme „zázračné účinky". Kratom je psychomodulační látka, ne lék. Naším úkolem je férová informace a kvalita produktu.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="static-section static-section--alt">
        <div class="container container--narrow">
            <header class="static-section__head static-section__head--center">
                <p class="static-section__eyebrow">Tým a hodnoty</p>
                <h2 class="static-section__title">Lidé za Vivadzenem</h2>
            </header>
            <p class="about-lead">Jsme malý tým s velkými ambicemi: postavit referenční značku kratomu v Česku. Spolupracujeme s odborníky na chemii, farmakologii a regulaci. Kontaktujte nás přímo — neschováváme se za call-centrem.</p>

            <ul class="about-values">
                <li><strong>Pravda nad marketingem.</strong> Negativní COA publikujeme.</li>
                <li><strong>Bezpečnost nad obratem.</strong> Kontrola věku není formalitou.</li>
                <li><strong>Rozvoj nad rychlostí.</strong> Učíme se z literatury a regulace.</li>
                <li><strong>Komunita nad transakcí.</strong> Otevřená Q&A, recenze bez cenzury.</li>
            </ul>
        </div>
    </section>

    <x-static.cta
        title="Začněte u nás osobně"
        text="Dvě prodejny v Praze, e-shop s laboratorními testy. Vyberte si, co vám sedí."
    />
</x-layouts.app>
