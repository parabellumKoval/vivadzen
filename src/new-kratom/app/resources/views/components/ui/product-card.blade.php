@props([
    'name' => 'Produkt',
    'vein' => 'green',          // red | green | white | yellow | mix
    'strainLabel' => 'Zelená žilka · Borneo',
    'mitragynin' => '1,42 % · jemně mletý',
    'price25' => 0,
    'price50' => 0,
    'rating' => null,
    'reviewsCount' => null,
    'badge' => null,            // ['variant' => 'sale', 'label' => '−10 %']
    'href' => '#',
    'image' => null,            // путь до картинки или null
    'imageLabel' => null,       // подпись для placeholder
    'slug' => '',
])

<article
    class="product-card"
    x-data="productCard({ price25: {{ (int)$price25 }}, price50: {{ (int)$price50 }}, slug: @js($slug) })"
>
    <a href="{{ $href }}" class="product-card__media" aria-label="{{ $name }}">
        @if($image)
            <img src="{{ $image }}" alt="{{ $name }}" loading="lazy" width="400" height="400" />
        @else
            <x-ui.placeholder shape="square" :label="$imageLabel ?? $name" icon="leaf" />
        @endif

        @if($badge)
            <div class="product-card__badges">
                <x-ui.badge :variant="$badge['variant']">{{ $badge['label'] }}</x-ui.badge>
            </div>
        @endif
    </a>

    <div class="product-card__body">
        <p class="product-card__eyebrow">
            <span class="vein-dot vein-dot--{{ $vein }}"></span>
            {{ $strainLabel }}
        </p>

        <h3 class="product-card__name">
            <a href="{{ $href }}">{{ $name }}</a>
        </h3>

        <p class="product-card__meta">Mitragynin {{ $mitragynin }}</p>

        @if($price25 && $price50)
            <div class="product-card__toggle" role="group" aria-label="Velikost balení">
                <button type="button" :aria-pressed="size === 25" :class="size === 25 && 'is-active'" @click="setSize(25)">25 g</button>
                <button type="button" :aria-pressed="size === 50" :class="size === 50 && 'is-active'" @click="setSize(50)">50 g</button>
            </div>
        @endif

        <div class="product-card__price-row">
            <div>
                <span class="product-card__price"><span x-text="price"></span> Kč</span>
                @if($price25 && $price50)
                    <div class="product-card__price-per"><span x-text="pricePerGram"></span> Kč / g</div>
                @endif
            </div>

            @if($rating)
                <div class="product-card__rating">
                    <span class="star"><x-ui.icon name="star" :size="14" /></span>
                    <span>{{ $rating }}</span>
                    @if($reviewsCount)
                        <span class="t-on-light-2">· {{ $reviewsCount }}</span>
                    @endif
                </div>
            @endif
        </div>

        <x-ui.button variant="primary" size="sm" block icon="shopping-bag" iconPosition="left"
                     class="product-card__cta" x-bind:disabled="busy" x-on:click.prevent="addToCart()">
            <span x-text="added ? '✓' : 'Přidat do košíku'"></span>
        </x-ui.button>
    </div>
</article>
