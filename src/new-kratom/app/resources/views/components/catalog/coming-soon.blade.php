@props([
    'placeholders' => [],
])

<section class="section section--cream-warm coming-soon" aria-labelledby="coming-soon-title">
    <div class="container">
        <header class="section-head section-head--center">
            <p class="t-overline section-head__eyebrow">PŘIPRAVUJEME</p>
            <h2 id="coming-soon-title" class="t-heading-xl t-on-light-accent">Brzy doplníme do sortimentu</h2>
            <p class="section-head__lead">Tyto odrůdy procházejí licencováním a laboratorními testy. Přihlaste se k notifikaci a budete první.</p>
        </header>

        <div class="coming-soon__grid">
            @foreach($placeholders as $p)
                <article class="placeholder-card">
                    <div class="placeholder-card__media" aria-hidden="true">
                        <x-ui.icon name="leaf" :size="48" />
                        <span class="badge badge--tag-terra placeholder-card__badge">BRZY</span>
                    </div>
                    <div class="placeholder-card__body">
                        <p class="placeholder-card__eyebrow">
                            <span class="vein-dot vein-dot--{{ $p['vein'] ?? 'green' }}"></span>
                            {{ $p['sub'] }}
                        </p>
                        <h3 class="placeholder-card__title">{{ $p['name'] }}</h3>
                        <p class="placeholder-card__spec">Mitragynin — TBD</p>
                        <x-ui.button variant="outline-light" size="md" block>
                            Upozornit při naskladnění
                        </x-ui.button>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="coming-soon__lead-capture">
            <h3 class="t-heading-md t-on-light-accent">Přidejte se na seznam</h3>
            <p class="t-body-md t-on-light-2">Dáme vám vědět o všech nových šaržích.</p>
            <form class="form-inline mt-4" novalidate>
                <x-ui.input type="email" name="email" placeholder="váš email" pill />
                <x-ui.button variant="primary" type="submit">Přihlásit se</x-ui.button>
            </form>
            <label class="check mt-3">
                <input type="checkbox">
                <span>Souhlasím s podmínkami GDPR.</span>
            </label>
        </div>
    </div>
</section>
