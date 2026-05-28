@php
    use App\Support\Locale;
    $count = (int) ($cart['count'] ?? 0);
@endphp

<x-layouts.app
    title="{{ __('site.cart.title') }} | Vivadzen"
    description="{{ __('site.cart.empty_text') }}"
    :announcement="false"
>
    @push('head')
        <meta name="robots" content="noindex,nofollow" />
    @endpush

    <section class="cart-page">
        <div class="container">
            <nav class="breadcrumbs" aria-label="Breadcrumbs">
                <a href="{{ Locale::url('/') }}">{{ __('site.breadcrumb_home') }}</a>
                <span class="breadcrumbs__sep">/</span>
                <span aria-current="page">{{ __('site.cart.breadcrumb') }}</span>
            </nav>

            <header class="cart-page__head">
                <div>
                    <h1 class="cart-page__title">{{ __('site.cart.title') }}</h1>
                    @if($count > 0)
                        <p class="cart-page__sub">
                            {{ trans_choice('site.cart.items_count', $count, ['count' => $count]) }}
                            · {{ number_format($cart['subtotal'], 0, ',', ' ') }} {{ __('site.currency') }}
                        </p>
                    @endif
                </div>
                <a href="{{ Locale::url('/kratom') }}" class="cart-page__continue">
                    ← {{ __('site.cart.continue_shopping') }}
                </a>
            </header>

            @if($count === 0)
                {{-- Empty state --}}
                <div class="cart-empty">
                    <div class="cart-empty__icon" aria-hidden="true">
                        <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19.2 2.96c.94 3.83.36 4.83-1.3 7-1.66 2.17-2.95 3.81-3.21 6.2"/>
                            <path d="M2 21c0-3 1.85-5.36 5.08-6"/>
                        </svg>
                    </div>
                    <h2 class="cart-empty__title">{{ __('site.cart.empty_title') }}</h2>
                    <p class="cart-empty__text">{{ __('site.cart.empty_text') }}</p>
                    <a href="{{ Locale::url('/kratom') }}" class="btn btn--primary btn--lg">
                        {{ __('site.cart.empty_cta') }}
                        <span class="btn__icon"><x-ui.icon name="arrow-right" /></span>
                    </a>
                </div>
            @else
                <div class="cart-page__grid" x-data="cartPage(@js($cart))">
                    {{-- Items column --}}
                    <div class="cart-items">
                        <template x-for="item in items" :key="item.key">
                            <article class="cart-item">
                                <a class="cart-item__media" :href="`/kratom/${item.slug}`">
                                    <img :src="item.image" :alt="item.name" loading="lazy" />
                                </a>

                                <div class="cart-item__body">
                                    <p class="cart-item__eyebrow">
                                        <span class="vein-dot" :class="`vein-dot--${item.vein}`"></span>
                                        <span x-text="item.color"></span>
                                        <template x-if="item.strain">
                                            <span>
                                                <span class="cart-item__sep">·</span>
                                                <span x-text="item.strain"></span>
                                            </span>
                                        </template>
                                    </p>
                                    <h3 class="cart-item__title">
                                        <a :href="`/kratom/${item.slug}`" x-text="item.name"></a>
                                    </h3>
                                    <p class="cart-item__meta">
                                        <template x-if="item.mitragynin">
                                            <span>Mitragynin <span x-text="item.mitragynin"></span> %</span>
                                        </template>
                                        <template x-if="item.grind">
                                            <span><span class="cart-item__sep">·</span><span x-text="item.grind"></span></span>
                                        </template>
                                        <template x-if="item.batch">
                                            <span><span class="cart-item__sep">·</span> šarže <span x-text="item.batch"></span></span>
                                        </template>
                                    </p>

                                    <div class="cart-item__row">
                                        <div class="cart-item__sizes" role="group" aria-label="Balení">
                                            <button
                                                type="button"
                                                class="cart-item__size"
                                                :class="item.size === 25 && 'is-active'"
                                                @click="changeSize(item.key, 25)"
                                                :disabled="busyKey === item.key || !item.price25"
                                            >25 <span x-text="item.unit || 'g'"></span></button>
                                            <button
                                                type="button"
                                                class="cart-item__size"
                                                :class="item.size === 50 && 'is-active'"
                                                @click="changeSize(item.key, 50)"
                                                :disabled="busyKey === item.key || !item.price50"
                                                x-show="item.price50 > 0"
                                            >50 <span x-text="item.unit || 'g'"></span></button>
                                        </div>

                                        <div class="cart-item__stepper" aria-label="{{ __('site.cart.qty') }}">
                                            <button type="button" @click="decrement(item.key)" :disabled="item.qty <= 1 || busyKey === item.key" aria-label="−">−</button>
                                            <span x-text="item.qty"></span>
                                            <button type="button" @click="increment(item.key)" :disabled="item.qty >= 99 || busyKey === item.key" aria-label="+">+</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="cart-item__aside">
                                    <p class="cart-item__price">
                                        <span x-text="format(item.price * item.qty)"></span> {{ __('site.currency') }}
                                    </p>
                                    <p class="cart-item__unit-price" x-show="item.qty > 1">
                                        <span x-text="format(item.price)"></span> {{ __('site.currency') }} / ks
                                    </p>
                                    <button type="button" class="cart-item__remove" @click="remove(item.key)" :disabled="busyKey === item.key">
                                        <x-ui.icon name="x" :size="14" />
                                        {{ __('site.cart.remove') }}
                                    </button>
                                </div>
                            </article>
                        </template>

                        {{-- Promo --}}
                        <div class="cart-promo">
                            <p class="cart-promo__label">{{ __('site.cart.promo_label') }}</p>
                            <div class="cart-promo__row" x-show="!promo">
                                <input
                                    type="text"
                                    class="cart-promo__input"
                                    x-model="promoCode"
                                    placeholder="{{ __('site.cart.promo_placeholder') }}"
                                    @keydown.enter.prevent="applyPromo()"
                                />
                                <button type="button" class="btn btn--solid-dark btn--md" @click="applyPromo()">
                                    {{ __('site.cart.promo_apply') }}
                                </button>
                            </div>
                            <p class="cart-promo__error" x-show="promoError">Neplatný kód.</p>
                            <div class="cart-promo__chip" x-show="promo" x-cloak>
                                <span class="cart-promo__chip-label">
                                    <x-ui.icon name="badge-check" :size="14" />
                                    <span x-text="promo?.label"></span>
                                </span>
                                <button type="button" @click="removePromo()" aria-label="Odebrat slevu">
                                    <x-ui.icon name="x" :size="14" />
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Summary column --}}
                    <aside class="cart-summary" aria-label="{{ __('site.cart.summary') }}">
                        <p class="cart-summary__eyebrow">{{ __('site.cart.summary') }}</p>

                        <dl class="cart-summary__rows">
                            <div>
                                <dt>{{ __('site.cart.subtotal') }}</dt>
                                <dd><span x-text="format(subtotal)"></span> {{ __('site.currency') }}</dd>
                            </div>
                            <div>
                                <dt>{{ __('site.cart.delivery') }}</dt>
                                <dd class="cart-summary__muted">{{ __('site.cart.delivery_later') }}</dd>
                            </div>
                            <div x-show="discount > 0">
                                <dt>{{ __('site.cart.discount') }}</dt>
                                <dd class="cart-summary__discount">−<span x-text="format(discount)"></span> {{ __('site.currency') }}</dd>
                            </div>
                        </dl>

                        <div class="cart-summary__total">
                            <span>{{ __('site.cart.total') }}</span>
                            <strong>
                                <span x-text="format(total)"></span> {{ __('site.currency') }}
                            </strong>
                        </div>
                        <p class="cart-summary__vat">{{ __('site.cart.vat') }}</p>

                        <a href="{{ Locale::url('/pokladna') }}" class="btn btn--primary btn--lg btn--block cart-summary__cta">
                            {{ __('site.cart.to_checkout') }}
                            <span class="btn__icon"><x-ui.icon name="arrow-right" /></span>
                        </a>

                        <ul class="cart-summary__pays" aria-label="Platební metody">
                            <li>Visa</li>
                            <li>MC</li>
                            <li>Apple Pay</li>
                            <li>Google Pay</li>
                            <li>QR</li>
                        </ul>

                        <ul class="cart-summary__trust">
                            <li><x-ui.icon name="shield-check" :size="16" /> {{ __('site.cart.trust_ssl') }}</li>
                            <li><x-ui.icon name="badge-check" :size="16" /> {{ __('site.cart.trust_return') }}</li>
                            <li><x-ui.icon name="shield-check" :size="16" /> {{ __('site.cart.trust_gdpr') }}</li>
                        </ul>
                    </aside>
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
