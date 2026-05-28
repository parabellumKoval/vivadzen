@props([
    'as'      => null,        // 'a' | 'button' (auto по наличию href)
    'href'    => null,
    'type'    => 'button',
    'variant' => 'primary',   // primary | secondary | outline-light | solid-dark | grass | terracotta | ghost | text
    'size'    => 'md',        // sm | md | lg
    'block'   => false,
    'icon'    => null,        // имя SVG из x-ui.icon или null
    'iconPosition' => 'right',
])

@php
    $tag = $as ?? ($href ? 'a' : 'button');
    $classes = collect([
        'btn',
        'btn--' . $variant,
        'btn--' . $size,
        $block ? 'btn--block' : null,
    ])->filter()->implode(' ');
@endphp

<{{ $tag }}
    @if($tag === 'a') href="{{ $href }}" @else type="{{ $type }}" @endif
    {{ $attributes->class($classes) }}
>
    @if($icon && $iconPosition === 'left')
        <span class="btn__icon"><x-ui.icon :name="$icon" /></span>
    @endif

    {{ $slot }}

    @if($icon && $iconPosition === 'right')
        <span class="btn__icon"><x-ui.icon :name="$icon" /></span>
    @endif
</{{ $tag }}>
