@props([
    'title' => 'Kategorie',
    'subtitle' => '',
    'href' => '#',
    'glyph' => 'green', // red | green | white | yellow | amber | terra | forest
    'glyphLabel' => null,
    'icon' => 'leaf',
])

<a href="{{ $href }}" {{ $attributes->class('cat-card') }}>
    <span class="cat-card__glyph">
        {{-- TODO: заменить на сгенерированный PNG (см. файл 02 §6 — Nano Banana промпт category-{slug}-circle-96.png) --}}
        <span class="glyph-placeholder glyph-placeholder--{{ $glyph }}" aria-hidden="true">
            <x-ui.icon :name="$icon" :size="32" />
        </span>
    </span>
    <span class="cat-card__title">{{ $title }}</span>
    @if($subtitle)
        <span class="cat-card__subtitle">{{ $subtitle }}</span>
    @endif
</a>
