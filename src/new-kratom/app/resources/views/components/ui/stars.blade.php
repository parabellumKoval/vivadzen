{{--
    Универсальный рейтинг звёздами. Используется в карточках товаров,
    отзывах, и везде, где нужно показать рейтинг 0..5.

    <x-ui.stars :rating="4.5" :size="14" />
--}}
@props([
    'rating' => 5,
    'size' => 14,
    'max' => 5,
    'label' => null,
])

@php
    $value = max(0, min((float) $rating, (int) $max));
    $rounded = (int) round($value);
    $aria = $label ?? 'Hodnocení ' . number_format($value, 1, ',', '') . ' z ' . $max;
@endphp

<span class="stars" role="img" aria-label="{{ $aria }}" {{ $attributes }}>
    @for ($i = 1; $i <= $max; $i++)
        <x-ui.icon
            name="star"
            :size="$size"
            :filled="true"
            class="stars__icon{{ $i <= $rounded ? ' is-on' : '' }}"
        />
    @endfor
</span>
