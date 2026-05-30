@props([
    'title' => 'Kategorie',
    'subtitle' => '',
    'href' => '#',
    'glyph' => 'green', // red | green | white | yellow | amber | terra | forest
    'glyphLabel' => null,
    'icon' => 'leaf',
    'image' => null,    // asset path → renders illustration instead of color glyph
    'media' => 'circle', // circle | cover | plate
    'deco' => null,     // декоративная ветка справа под медиа
])

<a href="{{ $href }}" {{ $attributes->class([
    'cat-card',
    'cat-card--cover' => $media === 'cover',
    'cat-card--plate' => $media === 'plate',
    'cat-card--region' => $media === 'region',
]) }}>
    @if($deco)
        <img class="cat-card__deco" src="{{ $deco }}" alt="" aria-hidden="true" loading="lazy" decoding="async">
    @endif
    @if($image)
        <span class="cat-card__glyph cat-card__glyph--img">
            <img src="{{ $image }}" alt="{{ $title }}" loading="lazy" decoding="async">
        </span>
    @else
        <span class="cat-card__glyph">
            <span class="glyph-placeholder glyph-placeholder--{{ $glyph }}" aria-hidden="true">
                <x-ui.icon :name="$icon" :size="32" />
            </span>
        </span>
    @endif
    <span class="cat-card__title">{{ $title }}</span>
    @if($subtitle)
        @if($media === 'plate')
            <span class="cat-card__divider" aria-hidden="true"></span>
        @endif
        <span class="cat-card__subtitle">{{ $subtitle }}</span>
    @endif
</a>
