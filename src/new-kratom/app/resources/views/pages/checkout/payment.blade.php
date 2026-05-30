@php
    use App\Support\Locale;
    $payment = $checkout['payment'] ?? [];
    $delivery = $checkout['delivery'] ?? [];
@endphp

<x-checkout.layout :step="2">
    <script id="cart-bootstrap" type="application/json">@json($cart)</script>

    <div class="container checkout-page">
        <div class="checkout-page__grid">
            <form
                method="POST"
                action="{{ Locale::url('/pokladna/platba') }}"
                class="checkout-form"
                x-data="checkout({ paymentMethod: '{{ $payment['payment_method'] ?? ($paymentMethods->first()->code ?? '') }}' })"
            >
                @csrf

                <section class="checkout-section">
                    <h2 class="checkout-section__title">{{ __('site.checkout.payment_title') }}</h2>

                    <div class="method-list" role="radiogroup">
                        @forelse($paymentMethods as $m)
                            @php
                                $icon = match($m->type) {
                                    'cod' => 'truck',
                                    'qr' => 'badge-check',
                                    'bank' => 'mail',
                                    'online' => 'shield-check',
                                    default => 'shield-check',
                                };
                                $title = $m->localized('name');
                                $desc = $m->localized('description');
                            @endphp
                            <label class="method" :class="paymentMethod === '{{ $m->code }}' && 'is-active'">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="{{ $m->code }}"
                                    x-model="paymentMethod"
                                    required
                                />
                                <span class="method__icon"><x-ui.icon :name="$icon" :size="22" /></span>
                                <span class="method__body">
                                    <span class="method__title">
                                        {{ $title }}
                                        @if($m->fee > 0)
                                            <span class="method__badge">+{{ $m->fee }} {{ __('site.currency') }}</span>
                                        @endif
                                    </span>
                                    <span class="method__desc">{{ $desc }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="checkout-empty">{{ __('site.checkout.no_payment_methods') }}</p>
                        @endforelse
                    </div>
                </section>

                {{-- Trust block --}}
                <section class="trust-card">
                    <div class="trust-card__head">
                        <span class="trust-card__icon">
                            <x-ui.icon name="shield-check" :size="22" />
                        </span>
                        <h3>{{ __('site.checkout.trust_block_title') }}</h3>
                    </div>
                    <ul>
                        <li>{{ __('site.checkout.trust_3ds') }}</li>
                        <li>{{ __('site.checkout.trust_ssl') }}</li>
                        <li>{{ __('site.checkout.trust_tokens') }}</li>
                        <li>{{ __('site.checkout.trust_return') }}</li>
                        <li>{{ __('site.checkout.trust_gdpr') }}</li>
                    </ul>
                </section>

                <div class="checkout-actions">
                    <a href="{{ Locale::url('/pokladna') }}" class="btn btn--ghost btn--md">
                        ← {{ __('site.checkout.back_to_delivery') }}
                    </a>
                    <button type="submit" class="btn btn--primary btn--lg">
                        {{ __('site.checkout.continue_to_confirm') }}
                        <span class="btn__icon"><x-ui.icon name="arrow-right" /></span>
                    </button>
                </div>
            </form>

            <x-checkout.order-summary :cart="$cart" />
        </div>
    </div>
</x-checkout.layout>
