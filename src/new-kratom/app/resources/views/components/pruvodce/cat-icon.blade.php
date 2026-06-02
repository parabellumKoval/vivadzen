{{-- Иконка категории /pruvodce. Сопоставляет slug → SVG.
     Все иконки в одном линейном стиле 2px stroke, viewBox 0 0 32 32. --}}
@props([
    'slug' => '',
    'size' => 32,
])

@php
    $icons = [
        // Ботаника и наука — лист кратома (Mitragyna speciosa): овально-ланцетная
        // форма, выраженный черешок и боковые жилки.
        'botanika-a-veda'      => '<path d="M16 3c-1.4 2.8-5.6 4-8.2 7.2C5 13.4 4 18 6.5 22.2c2.5 4.2 7.4 6 9.5 6.8 2.1-.8 7-2.6 9.5-6.8 2.5-4.2 1.5-8.8-1.3-12C21.6 7 17.4 5.8 16 3z"/><path d="M16 6.5v22"/><path d="M16 13c-1.6-1.4-3.6-2.2-5.6-2.4M16 13c1.6-1.4 3.6-2.2 5.6-2.4M16 18c-2-1.6-4.4-2.6-7-2.8M16 18c2-1.6 4.4-2.6 7-2.8M16 23c-1.6-1.2-3.6-2-5.6-2.2M16 23c1.6-1.2 3.6-2 5.6-2.2"/>',
        // История и культура — открытая книга / свиток.
        'historie-a-kultura'   => '<path d="M4 7c4-1 8-1 12 2 4-3 8-3 12-2v18c-4-1-8-1-12 2-4-3-8-3-12-2V7z"/><path d="M16 9v18"/>',
        // Законодательство — щит с галочкой (соответствует регуляции).
        'legislativa-cr'       => '<path d="M16 4l11 4v8c0 7-5 12-11 13C10 28 5 23 5 16V8l11-4z"/><path d="M11 16l4 4 7-8"/>',
        // Качество и безопасность — колба + капля чистоты.
        'kvalita-a-bezpecnost' => '<path d="M12 4h8"/><path d="M14 4v8.5L8.5 24a3 3 0 0 0 2.7 4.4h9.6A3 3 0 0 0 23.5 24L18 12.5V4"/><path d="M11 21h10"/><circle cx="16" cy="18" r="1.4" fill="currentColor"/>',
    ];

    $body = $icons[$slug] ?? '<circle cx="16" cy="16" r="10"/><path d="M16 11v6M16 21h.01"/>';
@endphp

<span {{ $attributes->merge(['class' => 'pruvodce-cat-card__icon', 'aria-hidden' => 'true']) }}>
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 32 32"
         fill="none" stroke="currentColor" stroke-width="2"
         stroke-linecap="round" stroke-linejoin="round">{!! $body !!}</svg>
</span>
