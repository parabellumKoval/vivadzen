{{-- S12 Newsletter — 04_HOMEPAGE.md §14 --}}
<section class="newsletter" aria-labelledby="nl-title">
    <div class="container newsletter__inner" x-data="newsletterForm">
        <h2 id="nl-title" class="t-heading-md t-on-light-accent">Aktuální šarže, novinky o regulaci PML a edukace</h2>
        <p class="t-body-md t-on-light-2">1× měsíčně, žádný spam. Můžete kdykoliv odhlásit.</p>

        <form class="newsletter__form form-inline" @submit.prevent="submit()" novalidate>
            <x-ui.input
                type="email"
                name="email"
                placeholder="vase.email@example.cz"
                pill
                x-model="email"
                ::disabled="state === 'submitting' || state === 'success'"
            />
            <x-ui.button
                variant="primary"
                type="submit"
                size="md"
                ::disabled="state === 'submitting'"
            >
                <span x-text="state === 'success' ? 'Hotovo' : 'Přihlásit se'"></span>
            </x-ui.button>
        </form>

        <p class="newsletter__hint">
            <x-ui.icon name="shield-check" :size="12" />
            Vaše údaje chráníme dle GDPR. Žádný spam.
        </p>

        <template x-if="state === 'error'">
            <p class="field__error" x-text="error"></p>
        </template>
        <template x-if="state === 'success'">
            <p class="field__success">Děkujeme! Potvrďte přihlášení ve své schránce.</p>
        </template>
    </div>
</section>
