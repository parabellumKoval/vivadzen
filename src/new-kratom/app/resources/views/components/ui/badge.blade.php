@props([
    'variant' => 'tag', // age | lab | licence | sale | out | subscription | express | tag | tag-amber | tag-terra
    'icon' => null,
])

<span {{ $attributes->class(['badge', 'badge--' . $variant]) }}>
    @if($icon)
        <x-ui.icon :name="$icon" :size="12" />
    @endif
    {{ $slot }}
</span>
