@php
    use App\Support\Locale;
    $payment = $checkout['payment'] ?? [];
    $delivery = $checkout['delivery'] ?? [];
    $hideCod = ($delivery['delivery_method'] ?? '') === 'express';
    $showStore = ($delivery['delivery_method'] ?? '') === 'pickup';
@endphp

<x-checkout.layout :step="2">
    <script id="cart-bootstrap" type="application/json">@json($cart)</script>

    <div class="container checkout-page">
        <div class="checkout-page__grid">
            <form
                method="POST"
                action="{{ Locale::url('/pokladna/platba') }}"
                class="checkout-form"
                x-data="checkout({ paymentMethod: '{{ $payment['payment_method'] ?? 'card' }}' })"
            >
                @csrf

                <section class="checkout-section">
                    <h2 class="checkout-section__title">{{ __('site.checkout.payment_title') }}</h2>

                    <div class="method-list" role="radiogroup">
                        @php
                            $methods = [
                                ['value' => 'card', 'icon' => 'shield-check', 'title' => __('site.checkout.payment_card'), 'desc' => __('site.checkout.payment_card_desc'), 'tags' => ['Visa','MC','Apple Pay','Google Pay']],
                                ['value' => 'qr',   'icon' => 'badge-check', 'title' => __('site.checkout.payment_qr'),   'desc' => __('site.checkout.payment_qr_desc')],
                                ['value' => 'bank', 'icon' => 'mail',        'title' => __('site.checkout.payment_bank'), 'desc' => __('site.checkout.payment_bank_desc')],
                            ];
                            if (! $hideCod) {
                                $methods[] = ['value' => 'cod', 'icon' => 'truck', 'title' => __('site.checkout.payment_cod'), 'desc' => str_replace(':fee', '39 ' . __('site.currency'), __('site.checkout.payment_cod_desc'))];
                            }
                            if ($showStore) {
                                $methods[] = ['value' => 'store', 'icon' => 'store', 'title' => __('site.checkout.payment_store'), 'desc' => __('site.checkout.payment_store_desc')];
                            }
                        @endphp

                        @foreach($methods as $m)
                            <label class="method" :class="paymentMethod === '{{ $m['value'] }}' && 'is-active'">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="{{ $m['value'] }}"
                                    x-model="paymentMethod"
                                    required
                                />
                                <span class="method__icon"><x-ui.icon :name="$m['icon']" :size="22" /></span>
                                <span class="method__body">
                                    <span class="method__title">{{ $m['title'] }}</span>
                                    <span class="method__desc">{{ $m['desc'] }}</span>
                                    @if(! empty($m['tags']))
                                        <span class="method__tags">
                                            @foreach($m['tags'] as $tag)
                                                <span>{{ $tag }}</span>
                                            @endforeach
                                        </span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
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
