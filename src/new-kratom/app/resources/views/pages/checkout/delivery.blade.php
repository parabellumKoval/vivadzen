@php
    use App\Support\Locale;
    $delivery = $checkout['delivery'] ?? [];
@endphp

<x-checkout.layout :step="1">
    <script id="cart-bootstrap" type="application/json">@json($cart)</script>

    <div class="container checkout-page">
        <div class="checkout-page__grid">
            <form
                method="POST"
                action="{{ Locale::url('/pokladna/doruceni') }}"
                class="checkout-form"
                x-data="checkout({ deliveryMethod: '{{ $delivery['delivery_method'] ?? 'courier' }}' })"
            >
                @csrf

                {{-- Contact --}}
                <section class="checkout-section">
                    <h2 class="checkout-section__title">{{ __('site.checkout.contact_title') }}</h2>
                    <div class="checkout-grid checkout-grid--2">
                        <label class="field">
                            <span class="field__label">{{ __('site.checkout.email') }} *</span>
                            <input type="email" name="email" required value="{{ $delivery['email'] ?? '' }}" class="field__input" autocomplete="email" />
                        </label>
                        <label class="field">
                            <span class="field__label">{{ __('site.checkout.phone') }} *</span>
                            <input type="tel" name="phone" required value="{{ $delivery['phone'] ?? '' }}" class="field__input" autocomplete="tel" />
                        </label>
                    </div>

                    <label class="checkbox checkbox--inline">
                        <input type="checkbox" name="marketing_consent" value="1" @checked(! empty($delivery['marketing_consent'])) />
                        <span>{{ __('site.checkout.marketing_consent') }}</span>
                    </label>
                </section>

                {{-- Address --}}
                <section class="checkout-section">
                    <h2 class="checkout-section__title">{{ __('site.checkout.address_title') }}</h2>
                    <div class="checkout-grid checkout-grid--2">
                        <label class="field">
                            <span class="field__label">{{ __('site.checkout.first_name') }} *</span>
                            <input type="text" name="first_name" required value="{{ $delivery['first_name'] ?? '' }}" class="field__input" autocomplete="given-name" />
                        </label>
                        <label class="field">
                            <span class="field__label">{{ __('site.checkout.last_name') }} *</span>
                            <input type="text" name="last_name" required value="{{ $delivery['last_name'] ?? '' }}" class="field__input" autocomplete="family-name" />
                        </label>
                    </div>

                    <label class="field">
                        <span class="field__label">{{ __('site.checkout.street') }} *</span>
                        <input type="text" name="street" required value="{{ $delivery['street'] ?? '' }}" class="field__input" autocomplete="street-address" />
                    </label>

                    <div class="checkout-grid checkout-grid--3">
                        <label class="field field--wide">
                            <span class="field__label">{{ __('site.checkout.city') }} *</span>
                            <input type="text" name="city" required value="{{ $delivery['city'] ?? '' }}" class="field__input" autocomplete="address-level2" />
                        </label>
                        <label class="field">
                            <span class="field__label">{{ __('site.checkout.zip') }} *</span>
                            <input type="text" name="zip" required value="{{ $delivery['zip'] ?? '' }}" class="field__input" autocomplete="postal-code" inputmode="numeric" />
                        </label>
                        <label class="field">
                            <span class="field__label">{{ __('site.checkout.country') }}</span>
                            <input type="text" name="country" value="{{ $delivery['country'] ?? 'Česká republika' }}" class="field__input" readonly />
                        </label>
                    </div>
                </section>

                {{-- Delivery method --}}
                <section class="checkout-section">
                    <h2 class="checkout-section__title">{{ __('site.checkout.delivery_title') }}</h2>

                    <div class="method-list" role="radiogroup">
                        @php
                            $methods = [
                                ['value' => 'courier',     'icon' => 'truck',  'price' => 89,  'title' => __('site.checkout.delivery_courier'),     'desc' => __('site.checkout.delivery_courier_desc'),     'free' => 1200],
                                ['value' => 'express',     'icon' => 'zap',    'price' => 290, 'title' => __('site.checkout.delivery_express'),     'desc' => __('site.checkout.delivery_express_desc'),     'badge' => 'EXPRESS'],
                                ['value' => 'pickup',      'icon' => 'store',  'price' => 0,   'title' => __('site.checkout.delivery_pickup'),      'desc' => __('site.checkout.delivery_pickup_desc')],
                                ['value' => 'zasilkovna',  'icon' => 'map-pin','price' => 79,  'title' => __('site.checkout.delivery_zasilkovna'),  'desc' => __('site.checkout.delivery_zasilkovna_desc')],
                            ];
                        @endphp

                        @foreach($methods as $m)
                            <label class="method" :class="deliveryMethod === '{{ $m['value'] }}' && 'is-active'">
                                <input
                                    type="radio"
                                    name="delivery_method"
                                    value="{{ $m['value'] }}"
                                    x-model="deliveryMethod"
                                    required
                                />
                                <span class="method__icon"><x-ui.icon :name="$m['icon']" :size="22" /></span>
                                <span class="method__body">
                                    <span class="method__title">
                                        {{ $m['title'] }}
                                        @if(! empty($m['badge']))
                                            <span class="method__badge">{{ $m['badge'] }}</span>
                                        @endif
                                    </span>
                                    <span class="method__desc">{{ $m['desc'] }}</span>
                                </span>
                                <span class="method__price">
                                    @if($m['price'] === 0)
                                        <strong>{{ __('site.cart.summary') === 'Order summary' ? 'Free' : 'Zdarma' }}</strong>
                                    @else
                                        <strong>{{ $m['price'] }} {{ __('site.currency') }}</strong>
                                        @if(! empty($m['free']))
                                            <small>{{ str_replace(':amount', $m['free'] . ' ' . __('site.currency'), __('site.checkout.free_above')) }}</small>
                                        @endif
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </section>

                <div class="checkout-actions">
                    <a href="{{ Locale::url('/kosik') }}" class="btn btn--ghost btn--md">
                        ← {{ __('site.checkout.back_to_cart') }}
                    </a>
                    <button type="submit" class="btn btn--primary btn--lg">
                        {{ __('site.checkout.continue_to_payment') }}
                        <span class="btn__icon"><x-ui.icon name="arrow-right" /></span>
                    </button>
                </div>
            </form>

            <x-checkout.order-summary :cart="$cart" />
        </div>
    </div>
</x-checkout.layout>
