@props([
    'product' => [],
])

@php
    $hasTwoSizes = !empty($product['price25']) && !empty($product['price50']);
    $unit        = $product['unit'] ?? 'g';
    $defaultSize = $hasTwoSizes ? 50 : 25;
    $defaultPrice = $product[$hasTwoSizes ? 'price50' : 'price25'];
@endphp

<div
    class="buy-box"
    x-data="productBuy({
        prices: { 25: {{ (int)($product['price25'] ?? 0) }}, 50: {{ (int)($product['price50'] ?? 0) }} },
        defaultSize: {{ $defaultSize }},
        unit: '{{ $unit }}',
        slug: @js($product['slug'] ?? ''),
    })"
>
    <div class="buy-box__eyebrow">
        <span class="badge badge--licence">
            <x-ui.icon name="shield-check" :size="12" /> LICENCE MZ ČR
        </span>
        @if($product['inStock'] ?? true)
            <span class="badge badge--subscription">
                <span class="buy-box__stock-dot"></span> SKLADEM V PRAZE
            </span>
        @else
            <span class="badge badge--out">Vyprodáno</span>
        @endif
        <span class="badge badge--lab">Testováno</span>
        <span class="badge badge--age">18+</span>
    </div>

    <h1 class="buy-box__title">{{ $product['name'] }}</h1>

    <p class="buy-box__subtitle">
        <span class="vein-dot vein-dot--{{ $product['vein'] ?? 'green' }}"></span>
        {{ $product['colorLabel'] }} · {{ $product['origin'] }} · Mitragyna speciosa, prášek
    </p>

    <div class="buy-box__rating">
        <x-ui.stars class="buy-box__stars" :rating="$product['rating']" :size="18" />
        <span class="buy-box__rating-text">
            <a href="#recenze" class="buy-box__rating-link">
                <strong>{{ str_replace('.', ',', (string)$product['rating']) }}</strong> · {{ $product['reviewsCount'] }} recenzí
            </a>
            <span class="buy-box__batch">· Šarže <a href="#laboratorni-test"><strong>{{ $product['batch'] }}</strong></a></span>
        </span>
    </div>

    <div class="buy-box__price">
        <span class="buy-box__price-main"><span x-text="totalPrice"></span> Kč</span>
        <span class="buy-box__price-unit">/ <span x-text="size"></span> {{ $unit }} · <span x-text="pricePerUnit"></span> Kč/{{ $unit }}</span>
    </div>

    @if($hasTwoSizes)
        <fieldset class="buy-box__sizes">
            <legend class="buy-box__label">Balení</legend>
            <div class="buy-box__seg">
                <button type="button" class="buy-box__seg-btn" :class="size === 25 && 'is-active'" x-on:click="setSize(25)">
                    <span class="buy-box__seg-size">25 {{ $unit }}</span>
                    <span class="buy-box__seg-price">· {{ $product['price25'] }} Kč</span>
                </button>
                <button type="button" class="buy-box__seg-btn" :class="size === 50 && 'is-active'" x-on:click="setSize(50)">
                    <span class="buy-box__seg-size">50 {{ $unit }}</span>
                    <span class="buy-box__seg-price">· {{ $product['price50'] }} Kč</span>
                </button>
            </div>
        </fieldset>
    @else
        <p class="buy-box__single-size">
            <span class="buy-box__label">Balení</span>
            <strong>{{ $product['unitSize'] ?? 25 }} {{ $unit }}</strong>
        </p>
    @endif

    <div class="buy-box__actions">
        <div class="buy-box__stepper" aria-label="Počet kusů">
            <button type="button" class="buy-box__step" x-on:click="qty > 1 && qty--" :disabled="qty === 1" aria-label="O jeden méně">
                <x-ui.icon name="minus" :size="16" />
            </button>
            <input type="number" min="1" max="99" class="buy-box__qty-input" x-model.number="qty" aria-label="Počet kusů">
            <button type="button" class="buy-box__step" x-on:click="qty < 99 && qty++" aria-label="O jeden více">
                <x-ui.icon name="plus" :size="16" />
            </button>
        </div>
        <x-ui.button variant="primary" size="lg" block class="buy-box__cta" x-on:click="addToCart()">
            <span class="btn__icon"><x-ui.icon name="shopping-bag" /></span>
            <span x-text="ctaLabel">Do košíku</span>
            <span class="buy-box__cta-price">· <span x-text="totalPrice"></span> Kč</span>
        </x-ui.button>
    </div>

    <button type="button" class="btn btn--outline-light btn--md btn--block buy-box__sub-cta">
        <span class="btn__icon"><x-ui.icon name="sparkles" /></span>
        Předplatné −10 %
    </button>

    <ul class="buy-box__trust">
        <li><span class="buy-box__trust-icon"><x-ui.icon name="truck" :size="18" /></span> Doručení po ČR · Express 180 min Praha &amp; Ostrava</li>
        <li><span class="buy-box__trust-icon"><x-ui.icon name="shield-check" :size="18" /></span> Bezpečná platba · 14 dní na vrácení</li>
        <li><span class="buy-box__trust-icon"><x-ui.icon name="store" :size="18" /></span> Osobní odběr Praha — zdarma, do 60 minut</li>
    </ul>

    <div class="buy-box__payments">
        <span class="buy-box__payment-chip">Visa</span>
        <span class="buy-box__payment-chip">Mastercard</span>
        <span class="buy-box__payment-chip">Apple Pay</span>
        <span class="buy-box__payment-chip">Google Pay</span>
        <span class="buy-box__payment-chip">QR</span>
        <span class="buy-box__payment-chip">Dobírka</span>
    </div>

    <div class="buy-box__age">
        <span class="badge badge--age">18+</span>
        <span>Není určeno osobám mladším 18 let.</span>
    </div>
</div>
