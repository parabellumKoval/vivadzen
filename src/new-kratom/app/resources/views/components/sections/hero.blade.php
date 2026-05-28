{{-- S3 Hero — 04_HOMEPAGE.md §3 --}}
<section class="hero" aria-labelledby="hero-title">
    <div
        aria-hidden="true"
        style="
            position: absolute;
            inset: 0;
            z-index: 0;
            background: url('{{ asset('assets/ai-generated/hero/bg-wide.png') }}') center center / cover no-repeat;
            pointer-events: none;
        "
    ></div>

    <div
        aria-hidden="true"
        style="
            position: absolute;
            inset: 0;
            z-index: 0;
            background: linear-gradient(90deg, rgba(11, 32, 22, 0.60) 0%, rgba(11, 32, 22, 0.52) 34%, rgba(11, 32, 22, 0.36) 56%, rgba(11, 32, 22, 0.12) 74%, rgba(11, 32, 22, 0) 100%);
            pointer-events: none;
        "
    ></div>

    <div class="container" style="position: relative; z-index: 1;">
        <div class="hero__grid">
            <div style="position: relative; z-index: 2; max-width: 56rem;">
                <p class="t-overline hero__eyebrow">VIVADZEN — LICENCOVANÝ E-SHOP</p>

                <h1 id="hero-title" class="t-display-xl hero__title t-two-tone">
                    <span class="t-two-tone__line-1">Kratom s licencí MZ ČR</span>
                    <span class="t-two-tone__line-2">pro dospělé v Praze i online</span>
                </h1>

                <p class="t-body-lg hero__sub">
                    Specializovaný e-shop napojený na <strong>dvě kamenné prodejny v Praze</strong>.
                    Každá šarže laboratorně testovaná v akreditované laboratoři
                    <strong>VŠCHT Praha</strong> dle normy ISO 17025. Doručení po celé ČR,
                    EXPRESS 180 minut v Praze a Ostravě.
                </p>

                <div class="hero__ctas">
                    <x-ui.button href="/kratom" variant="primary" size="lg" icon="arrow-right">
                        Prohlédnout kratom
                    </x-ui.button>
                    <x-ui.button href="/prodejny" variant="secondary" size="lg">
                        Naše prodejny v Praze
                    </x-ui.button>
                </div>

                <div class="hero__metrics" role="list">
                    <div class="hero__metric" role="listitem">
                        <span class="t-metric-lg num">2 500+</span>
                        <span class="label">Spokojených zákazníků</span>
                    </div>
                    <div class="hero__metric" role="listitem">
                        <span class="t-metric-lg num">100 %</span>
                        <span class="label">Šarží laboratorně testováno</span>
                    </div>
                    <div class="hero__metric" role="listitem">
                        <span class="t-metric-lg num">2</span>
                        <span class="label">Prodejny v Praze</span>
                    </div>
                </div>
            </div>

            <div
                class="hero__products"
                aria-hidden="true"
                style="
                    position: absolute;
                    right: -300px;
                    bottom: -230px;
                    z-index: 1;
                    width: min(69vw, 57rem);
                    pointer-events: none;
                "
            >
                <img
                    class="hero__products-image"
                    src="{{ asset('assets/ai-generated/hero/products.png') }}"
                    alt=""
                    loading="eager"
                    fetchpriority="high"
                    width="1122"
                    height="1402"
                    style="
                        display: block;
                        width: 100%;
                        height: auto;
                        filter: drop-shadow(0 30px 50px rgba(8, 26, 18, 0.28));
                    "
                />
            </div>

            {{-- Fallback/rollback:
            <div class="hero__visual">
                <img
                    class="hero__image"
                    src="{{ asset('assets/ai-generated/hero/hero-1-1.png') }}"
                    alt="Detail listu kratomu v teplém světle"
                    loading="eager"
                    fetchpriority="high"
                    width="1536"
                    height="1024"
                />
            </div>
            --}}
        </div>
    </div>
</section>
