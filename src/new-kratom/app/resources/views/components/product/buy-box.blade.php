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
        <span class="buy-box__chip">
            <span class="vein-dot vein-dot--{{ $product['vein'] ?? 'green' }}"></span>
            {{ $product['colorLabel'] }} · {{ $product['origin'] }}
        </span>
        <span class="badge badge--licence">
            <x-ui.icon name="shield-check" :size="12" /> LICENCE MZ ČR
        </span>
    </div>

    <h1 class="buy-box__title">{{ $product['name'] }}</h1>

    <div class="buy-box__rating">
        <span class="buy-box__stars" aria-label="Hodnocení {{ $product['rating'] }} z 5">
            @for($i = 0; $i < 5; $i++)
                <x-ui.icon name="star" :size="18" />
            @endfor
        </span>
        <a href="#recenze" class="buy-box__rating-link">
            <strong>{{ str_replace('.', ',', (string)$product['rating']) }}</strong> · {{ $product['reviewsCount'] }} recenzí · {{ $product['questionsCount'] }} otázek
        </a>
    </div>

    <p class="buy-box__meta">
        Mitragynin {{ $product['mitragynin'] }} % · {{ $product['grind'] }} · <a href="#laboratorni-test">šarže {{ $product['batch'] }}</a>
    </p>

    @if($hasTwoSizes)
        <fieldset class="buy-box__sizes">
            <legend class="buy-box__label">Balení</legend>
            <div class="buy-box__seg">
                <button type="button" class="buy-box__seg-btn" :class="size === 25 && 'is-active'" x-on:click="setSize(25)">
                    <span class="buy-box__seg-size">25 {{ $unit }}</span>
                    <span class="buy-box__seg-price">{{ $product['price25'] }} Kč</span>
                </button>
                <button type="button" class="buy-box__seg-btn" :class="size === 50 && 'is-active'" x-on:click="setSize(50)">
                    <span class="buy-box__seg-size">50 {{ $unit }}</span>
                    <span class="buy-box__seg-price">{{ $product['price50'] }} Kč</span>
                </button>
            </div>
        </fieldset>
    @else
        <p class="buy-box__single-size">
            <span class="buy-box__label">Balení</span>
            <strong>{{ $product['unitSize'] ?? 25 }} {{ $unit }}</strong>
        </p>
    @endif

    <div class="buy-box__qty">
        <span class="buy-box__label">Počet</span>
        <div class="buy-box__stepper">
            <button type="button" class="buy-box__step" x-on:click="qty > 1 && qty--" :disabled="qty === 1" aria-label="O jeden méně">
                <x-ui.icon name="x" :size="14" />
            </button>
            <input type="number" min="1" max="99" class="buy-box__qty-input" x-model.number="qty" aria-label="Počet kusů">
            <button type="button" class="buy-box__step" x-on:click="qty < 99 && qty++" aria-label="O jeden více">
                <x-ui.icon name="arrow-right" :size="14" />
            </button>
        </div>
    </div>

    <div class="buy-box__price">
        <span class="buy-box__price-main"><span x-text="totalPrice"></span> Kč</span>
        <span class="buy-box__price-unit">(<span x-text="pricePerUnit"></span> Kč / {{ $unit }})</span>
    </div>

    <x-ui.button variant="primary" size="lg" block icon="arrow-right" x-on:click="addToCart()">
        <span x-text="ctaLabel">Přidat do košíku</span>
    </x-ui.button>

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
