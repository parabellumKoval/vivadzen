@props([
    'level' => null,   // level array {name, icon, color, min}
    'size' => 'sm',
])

@if($level)
    <span
        {{ $attributes->class(['flevel', 'flevel--' . $size]) }}
        style="--flevel-color: {{ $level['color'] }}"
        title="Úroveň: {{ $level['name'] }}"
    >
        <span class="flevel__icon" aria-hidden="true">{{ $level['icon'] }}</span>
        <span class="flevel__name">{{ $level['name'] }}</span>
    </span>
@endif
