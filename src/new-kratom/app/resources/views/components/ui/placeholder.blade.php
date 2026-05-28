@props([
    'variant' => null,        // dark | null
    'shape'   => null,         // square | portrait | wide | hero | null
    'label'   => 'Image placeholder',
    'hint'    => null,
    'icon'    => 'sparkles',
])

@php
    $classes = collect([
        'placeholder',
        $variant ? 'placeholder--' . $variant : null,
        $shape   ? 'placeholder--' . $shape   : null,
    ])->filter()->implode(' ');
@endphp

<div {{ $attributes->class($classes) }} role="img" aria-label="{{ $label }}">
    <div class="placeholder__inner">
        <div class="placeholder__icon"><x-ui.icon :name="$icon" :size="48" /></div>
        <div class="placeholder__label">{{ $label }}</div>
        @if($hint)
            <div class="placeholder__hint">{{ $hint }}</div>
        @endif
    </div>
</div>
