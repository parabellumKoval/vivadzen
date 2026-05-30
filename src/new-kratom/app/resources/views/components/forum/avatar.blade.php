@props([
    'user' => null,        // user array or null
    'size' => 'md',        // xs | sm | md | lg | xl
    'href' => null,        // make clickable
    'showLevel' => false,  // show level icon overlay
    'levels' => [],
])

@php
    $tag = $href ? 'a' : 'span';
    $bg = $user['avatarColor'] ?? 'var(--c-forest-600)';
    $initials = collect(explode(' ', $user['name'] ?? '?'))
        ->map(fn ($w) => mb_substr($w, 0, 1))
        ->take(2)
        ->implode('');
    $level = $user && isset($levels[$user['level'] ?? null]) ? $levels[$user['level']] : null;
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->class(['favatar', 'favatar--' . $size]) }}
    style="--favatar-bg: {{ $bg }}"
    @if($user) aria-label="{{ $user['name'] }}" @endif
>
    @if($user && !empty($user['avatar']))
        <img class="favatar__img" src="{{ $user['avatar'] }}" alt="" />
    @else
        <span class="favatar__initials" aria-hidden="true">{{ $initials ?: '?' }}</span>
    @endif

    @if($showLevel && $level)
        <span class="favatar__level" aria-hidden="true" title="{{ $level['name'] }}">{{ $level['icon'] }}</span>
    @endif
</{{ $tag }}>
