@props(['cart' => [], 'compact' => false])

<aside class="checkout-summary" aria-label="{{ __('site.cart.summary') }}">
    <p class="checkout-summary__eyebrow">{{ __('site.cart.summary') }}</p>

    @unless($compact)
        <ul class="checkout-summary__items">
            @foreach($cart['items'] as $item)
                <li class="checkout-summary__item">
                    <span class="checkout-summary__item-media">
                        @if($item['image'])
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" />
                        @endif
                        <span class="checkout-summary__item-qty">{{ $item['qty'] }}</span>
                    </span>
                    <span class="checkout-summary__item-body">
                        <strong>{{ $item['name'] }}</strong>
                        <small>{{ $item['size'] }} {{ $item['unit'] ?? 'g' }}</small>
                    </span>
                    <span class="checkout-summary__item-price">
                        {{ number_format($item['price'] * $item['qty'], 0, ',', ' ') }} {{ __('site.currency') }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endunless

    <dl class="checkout-summary__rows">
        <div>
            <dt>{{ __('site.cart.subtotal') }}</dt>
            <dd>{{ number_format($cart['subtotal'], 0, ',', ' ') }} {{ __('site.currency') }}</dd>
        </div>
        @if(($cart['discount'] ?? 0) > 0)
            <div>
                <dt>{{ __('site.cart.discount') }}{{ $cart['promo'] ? ' (' . $cart['promo']['code'] . ')' : '' }}</dt>
                <dd class="checkout-summary__discount">−{{ number_format($cart['discount'], 0, ',', ' ') }} {{ __('site.currency') }}</dd>
            </div>
        @endif
        <div>
            <dt>{{ __('site.cart.delivery') }}</dt>
            <dd class="checkout-summary__muted">{{ __('site.cart.delivery_later') }}</dd>
        </div>
    </dl>

    <div class="checkout-summary__total">
        <span>{{ __('site.cart.total') }}</span>
        <strong>{{ number_format($cart['total'], 0, ',', ' ') }} {{ __('site.currency') }}</strong>
    </div>
    <p class="checkout-summary__vat">{{ __('site.cart.vat') }}</p>

    <ul class="checkout-summary__trust">
        <li><x-ui.icon name="shield-check" :size="16" /> {{ __('site.checkout.trust_ssl') }}</li>
        <li><x-ui.icon name="badge-check" :size="16" /> {{ __('site.checkout.trust_return') }}</li>
        <li><x-ui.icon name="shield-check" :size="16" /> {{ __('site.checkout.trust_gdpr') }}</li>
    </ul>
</aside>
