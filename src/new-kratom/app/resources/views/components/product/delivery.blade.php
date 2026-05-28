<section class="section section--cream delivery-section" id="doprava" aria-labelledby="delivery-title">
    <div class="container">
        <h2 id="delivery-title" class="sr-only">Doprava a platba</h2>

        <div class="delivery-grid">
            <div class="delivery-col">
                <h3 class="t-heading-lg t-on-light-accent">Doručení</h3>
                <ul class="delivery-cards">
                    <li class="delivery-card">
                        <span class="delivery-card__icon"><x-ui.icon name="truck" :size="24" /></span>
                        <div>
                            <strong>Standardní doručení po ČR</strong>
                            <p>1–3 pracovní dny — od 89 Kč (zdarma od 1 200 Kč)</p>
                        </div>
                    </li>
                    <li class="delivery-card">
                        <span class="delivery-card__icon delivery-card__icon--amber"><x-ui.icon name="zap" :size="24" /></span>
                        <div>
                            <strong>Express 180 min — Praha &amp; Ostrava</strong>
                            <p>Do 3 hodin — 290 Kč</p>
                        </div>
                    </li>
                    <li class="delivery-card">
                        <span class="delivery-card__icon"><x-ui.icon name="store" :size="24" /></span>
                        <div>
                            <strong>Osobní odběr Praha</strong>
                            <p>Do 60 minut — zdarma</p>
                        </div>
                    </li>
                </ul>
                <p class="delivery-section__note">
                    Kurýr ověří váš věk 18+ při převzetí dle zákona č. 167/1998 Sb.
                </p>
                <a href="{{ \App\Support\Locale::url('/doruceni') }}" class="delivery-section__link">Více o doručení →</a>
            </div>

            <div class="delivery-col">
                <h3 class="t-heading-lg t-on-light-accent">Platba</h3>
                <ul class="payment-grid">
                    <li><span class="payment-grid__icon"><x-ui.icon name="shield-check" :size="20" /></span> Online kartou (Visa, MC, Apple Pay, Google Pay)</li>
                    <li><span class="payment-grid__icon"><x-ui.icon name="sparkles" :size="20" /></span> QR platba</li>
                    <li><span class="payment-grid__icon"><x-ui.icon name="mail" :size="20" /></span> Bankovní převod</li>
                    <li><span class="payment-grid__icon"><x-ui.icon name="shopping-bag" :size="20" /></span> Dobírka při převzetí</li>
                    <li><span class="payment-grid__icon"><x-ui.icon name="store" :size="20" /></span> Při osobním odběru</li>
                    <li><span class="payment-grid__icon"><x-ui.icon name="shield-check" :size="20" /></span> 3D Secure, SSL šifrování</li>
                </ul>
                <p class="delivery-section__note">
                    Zaručujeme bezpečnost vašich platebních údajů. Citlivá data nikdy neukládáme.
                </p>
            </div>
        </div>
    </div>
</section>
