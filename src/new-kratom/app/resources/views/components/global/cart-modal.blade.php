@php
    use App\Support\Locale;
@endphp

<div
    x-data="cartModal"
    x-cloak
    class="cart-modal"
    :class="open && 'is-open'"
    @keydown.escape.window="open = false"
>
    <div class="cart-modal__backdrop" x-show="open" x-transition.opacity @click="open = false"></div>

    <aside
        class="cart-modal__panel"
        x-show="open"
        x-transition:enter="cart-modal__panel--enter"
        x-transition:enter-start="cart-modal__panel--enter-start"
        x-transition:enter-end="cart-modal__panel--enter-end"
        x-transition:leave="cart-modal__panel--leave"
        x-transition:leave-start="cart-modal__panel--leave-start"
        x-transition:leave-end="cart-modal__panel--leave-end"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cart-modal-title"
    >
        <header class="cart-modal__head">
            <h2 id="cart-modal-title" class="cart-modal__title">
                {{ __('site.cart.title') }}
                <span x-show="count > 0" class="cart-modal__count">(<span x-text="count"></span>)</span>
            </h2>
            <button type="button" class="cart-modal__close" @click="open = false" aria-label="{{ __('site.header.close') }}">
                <x-ui.icon name="x" />
            </button>
        </header>

        <div class="cart-modal__body" x-show="count > 0" x-cloak>
            <ul class="cart-modal__items">
                <template x-for="item in items" :key="item.key">
                    <li class="cart-modal__item">
                        <a class="cart-modal__item-media" :href="`/kratom/${item.slug}`">
                            <img :src="item.image" :alt="item.name" loading="lazy" />
                        </a>

                        <div class="cart-modal__item-body">
                            <p class="cart-modal__item-eyebrow">
                                <span class="vein-dot" :class="`vein-dot--${item.vein || 'green'}`"></span>
                                <span x-text="item.color"></span>
                                <template x-if="item.strain">
                                    <span>· <span x-text="item.strain"></span></span>
                                </template>
                            </p>
                            <h3 class="cart-modal__item-title">
                                <a :href="`/kratom/${item.slug}`" x-text="item.name"></a>
                            </h3>

                            <div class="cart-modal__item-row">
                                <div class="cart-modal__sizes" role="group" aria-label="Balení">
                                    <button
                                        type="button"
                                        :class="item.size === 25 && 'is-active'"
                                        @click="changeSize(item.key, 25)"
                                        :disabled="busyKey === item.key || !item.price25"
                                    >25 <span x-text="item.unit || 'g'"></span></button>
                                    <button
                                        type="button"
                                        :class="item.size === 50 && 'is-active'"
                                        @click="changeSize(item.key, 50)"
                                        :disabled="busyKey === item.key || !item.price50"
                                        x-show="item.price50 > 0"
                                    >50 <span x-text="item.unit || 'g'"></span></button>
                                </div>

                                <div class="cart-modal__stepper" aria-label="Počet">
                                    <button type="button" @click="decrement(item.key)" :disabled="item.qty <= 1 || busyKey === item.key" aria-label="−">−</button>
                                    <span x-text="item.qty"></span>
                                    <button type="button" @click="increment(item.key)" :disabled="item.qty >= 99 || busyKey === item.key" aria-label="+">+</button>
                                </div>
                            </div>
                        </div>

                        <div class="cart-modal__item-aside">
                            <p class="cart-modal__item-price">
                                <span x-text="format(item.price * item.qty)"></span> {{ __('site.currency') }}
                            </p>
                            <button type="button" class="cart-modal__item-remove" @click="remove(item.key)" :disabled="busyKey === item.key" aria-label="{{ __('site.cart.remove') }}">
                                <x-ui.icon name="x" :size="14" />
                            </button>
                        </div>
                    </li>
                </template>
            </ul>

            <div class="cart-modal__promo">
                <p class="cart-modal__promo-label">{{ __('site.cart.promo_label') }}</p>
                <div class="cart-modal__promo-row" x-show="!promo">
                    <input
                        type="text"
                        class="cart-modal__promo-input"
                        x-model="promoCode"
                        placeholder="{{ __('site.cart.promo_placeholder') }}"
                        @keydown.enter.prevent="applyPromo()"
                    />
                    <button type="button" class="btn btn--solid-dark btn--md" @click="applyPromo()">
                        {{ __('site.cart.promo_apply') }}
                    </button>
                </div>
                <p class="cart-modal__promo-error" x-show="promoError">Neplatný kód.</p>
                <div class="cart-modal__promo-chip" x-show="promo" x-cloak>
                    <span><x-ui.icon name="badge-check" :size="14" /> <span x-text="promo?.label"></span></span>
                    <button type="button" @click="removePromo()" aria-label="Odebrat slevu">
                        <x-ui.icon name="x" :size="14" />
                    </button>
                </div>
            </div>
        </div>

        <div class="cart-modal__empty" x-show="count === 0">
            <div class="cart-modal__empty-icon" aria-hidden="true">
                <x-ui.icon name="shopping-bag" :size="48" />
            </div>
            <h3 class="cart-modal__empty-title">{{ __('site.cart.empty_title') }}</h3>
            <p class="cart-modal__empty-text">{{ __('site.cart.empty_text') }}</p>
            <a href="{{ Locale::url('/kratom') }}" class="btn btn--primary btn--lg btn--block">
                {{ __('site.cart.empty_cta') }}
            </a>
        </div>

        <footer class="cart-modal__foot" x-show="count > 0" x-cloak>
            <dl class="cart-modal__totals">
                <div>
                    <dt>{{ __('site.cart.subtotal') }}</dt>
                    <dd><span x-text="format(subtotal)"></span> {{ __('site.currency') }}</dd>
                </div>
                <div x-show="discount > 0">
                    <dt>{{ __('site.cart.discount') }}</dt>
                    <dd>−<span x-text="format(discount)"></span> {{ __('site.currency') }}</dd>
                </div>
                <div class="cart-modal__total">
                    <dt>{{ __('site.cart.total') }}</dt>
                    <dd><strong><span x-text="format(total)"></span> {{ __('site.currency') }}</strong></dd>
                </div>
            </dl>
            <a href="{{ Locale::url('/pokladna') }}" class="btn btn--primary btn--lg btn--block">
                {{ __('site.cart.to_checkout') }}
                <span class="btn__icon"><x-ui.icon name="arrow-right" /></span>
            </a>
            <a href="{{ Locale::url('/kosik') }}" class="cart-modal__view-cart">{{ __('site.cart.continue_shopping') }}</a>
        </footer>
    </aside>
</div>
