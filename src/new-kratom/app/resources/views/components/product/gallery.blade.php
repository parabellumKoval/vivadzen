@props([
    'name'    => '',
    'gallery' => [],     // array of paths
    'batch'   => '',
    'inStock' => true,
])

<div class="product-gallery" x-data="{ active: 0, total: {{ count($gallery) }} }">
    <div class="product-gallery__stage">
        @foreach($gallery as $i => $src)
            <button
                type="button"
                class="product-gallery__slide"
                :class="active === {{ $i }} && 'is-active'"
                x-show="active === {{ $i }}"
                aria-label="Zvětšit fotografii {{ $i + 1 }}"
            >
                <img src="{{ asset(ltrim($src, '/')) }}" alt="{{ $name }} — fotografie {{ $i + 1 }}" />
            </button>
        @endforeach

        <div class="product-gallery__badges">
            @if($inStock)
                <span class="badge badge--subscription">Skladem</span>
            @else
                <span class="badge badge--out">Vyprodáno</span>
            @endif
            @if($batch)
                <span class="badge badge--lab">Lab-testováno · šarže {{ $batch }}</span>
            @endif
        </div>
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
                    <img src="{{ asset(ltrim($src, '/')) }}" alt="" loading="lazy" />
                </button>
            @endforeach
        </div>
    @endif
</div>
