@php use App\Support\Locale; @endphp

<x-layouts.app
    title="{{ __('site.pages.kontakt') }} | Vivadzen"
    description="Kontakt na Vivadzen — e-mail, telefon, prodejny v Praze, fakturační údaje."
>
    <x-static.hero
        icon="mail"
        :eyebrow="__('site.pages.kontakt')"
        title="Jsme tu pro vás"
        lead="Píšete nám e-mail, voláte na podporu nebo zajdete na jednu z prodejen. Odpovídáme rychle a věcně."
    />

    <section class="static-section">
        <div class="container">
            <div class="contact-grid">
                <article class="contact-card">
                    <div class="contact-card__icon"><x-ui.icon name="mail" :size="24" /></div>
                    <h3>E-mail</h3>
                    <p><a href="mailto:info@vivadzen.com">info@vivadzen.com</a></p>
                    <p class="contact-card__hint">Odpovídáme do 4 h v pracovní době.</p>
                </article>

                <article class="contact-card">
                    <div class="contact-card__icon"><x-ui.icon name="zap" :size="24" /></div>
                    <h3>Telefon</h3>
                    <p><a href="tel:+420000000000">+420 000 000 000</a></p>
                    <p class="contact-card__hint">Po–Pá 10:00 — 18:00</p>
                </article>

                <article class="contact-card">
                    <div class="contact-card__icon"><x-ui.icon name="map-pin" :size="24" /></div>
                    <h3>Prodejny v Praze</h3>
                    <p>Karlovo nám. 5, Praha 2</p>
                    <p>Bělohorská 100, Praha 6</p>
                    <p class="contact-card__hint"><a href="{{ Locale::url('/prodejny') }}">Otevírací doba a mapa →</a></p>
                </article>

                <article class="contact-card">
                    <div class="contact-card__icon"><x-ui.icon name="shield-check" :size="24" /></div>
                    <h3>Fakturační údaje</h3>
                    <p><strong>Vivadzen s.r.o.</strong></p>
                    <p>IČO: 00000000 · DIČ: CZ00000000</p>
                    <p class="contact-card__hint">Sídlo: Praha, ČR</p>
                </article>
            </div>
        </div>
    </section>

    <section class="static-section static-section--alt">
        <div class="container container--narrow">
            <header class="static-section__head static-section__head--center">
                <p class="static-section__eyebrow">Napište nám</p>
                <h2 class="static-section__title">Pošlete zprávu</h2>
            </header>

            <form class="contact-form" action="#" method="POST" novalidate>
                <div class="checkout-grid checkout-grid--2">
                    <label class="field">
                        <span class="field__label">Jméno *</span>
                        <input type="text" class="field__input" required />
                    </label>
                    <label class="field">
                        <span class="field__label">E-mail *</span>
                        <input type="email" class="field__input" required />
                    </label>
                </div>
                <label class="field">
                    <span class="field__label">Předmět</span>
                    <input type="text" class="field__input" />
                </label>
                <label class="field">
                    <span class="field__label">Zpráva *</span>
                    <textarea class="field__input field__input--textarea" rows="5" required></textarea>
                </label>
                <label class="checkbox">
                    <input type="checkbox" required />
                    <span>Souhlasím s <a href="{{ Locale::url('/ochrana-osobnich-udaju') }}">zpracováním osobních údajů</a>. *</span>
                </label>
                <button type="submit" class="btn btn--primary btn--lg">
                    Odeslat zprávu
                    <span class="btn__icon"><x-ui.icon name="arrow-right" /></span>
                </button>
            </form>
        </div>
    </section>
</x-layouts.app>
