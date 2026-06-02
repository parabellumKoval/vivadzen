@props([
    'name'    => '',
    'gallery' => [],     // array of paths
    'batch'   => '',
    'inStock' => true,
])

<div class="product-gallery" x-data="{ active: 0, total: {{ count($gallery) }}, prev() { this.active = (this.active - 1 + this.total) % this.total }, next() { this.active = (this.active + 1) % this.total } }">
    <div class="product-gallery__stage">
        @foreach($gallery as $i => $src)
            <button
                type="button"
                class="product-gallery__slide"
                :class="active === {{ $i }} && 'is-active'"
                x-show="active === {{ $i }}"
                aria-label="Zvětšit fotografii {{ $i + 1 }}"
            >
                <img src="{{ \Illuminate\Support\Str::startsWith($src, ['http://', 'https://', '//']) ? $src : asset(ltrim($src, '/')) }}" alt="{{ $name }} — fotografie {{ $i + 1 }}" />
            </button>
        @endforeach

        @if(count($gallery) > 1)
            <button
                type="button"
                class="product-gallery__arrow product-gallery__arrow--prev"
                x-on:click.stop="prev()"
                aria-label="Předchozí fotografie"
            >
                <x-ui.icon name="arrow-left" :size="18" />
            </button>
            <button
                type="button"
                class="product-gallery__arrow product-gallery__arrow--next"
                x-on:click.stop="next()"
                aria-label="Další fotografie"
            >
                <x-ui.icon name="arrow-right" :size="18" />
            </button>
        @endif
    </div>

    @if(count($gallery) > 1)
        <div class="product-gallery__thumbs" role="tablist">
            @foreach($gallery as $i => $src)
                <button
                    type="button"
                    role="tab"
                    class="product-gallery__thumb"
                    :class="active === {{ $i }} && 'is-active'"
                    :aria-selected="active === {{ $i }}"
                    x-on:click="active = {{ $i }}"
                    aria-label="Zobrazit fotografii {{ $i + 1 }}"
                >
                    <img src="{{ \Illuminate\Support\Str::startsWith($src, ['http://', 'https://', '//']) ? $src : asset(ltrim($src, '/')) }}" alt="" loading="lazy" />
                </button>
            @endforeach
        </div>
    @endif
</div>
