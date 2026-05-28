@props([
    'renditions' => null,     // ['avif' => [480 => url, ...], 'webp' => [...], 'jpg' => [...]]
    'src' => null,            // fallback (если renditions нет)
    'alt' => '',
    'sizes' => '(max-width: 768px) 100vw, 50vw',
    'loading' => 'lazy',      // первое изображение на странице — eager + fetchpriority=high
    'fetchpriority' => null,
    'class' => '',
    'width' => null,          // обязательно для предотвращения CLS!
    'height' => null,
])

@php
    // Если renditions не передан — деградируем до тэга img
    $hasRenditions = is_array($renditions) && !empty($renditions);

    // Собираем srcset строки для каждого формата
    $srcset = [];
    if ($hasRenditions) {
        foreach (['avif', 'webp', 'jpg'] as $fmt) {
            if (!isset($renditions[$fmt])) continue;
            $pieces = [];
            foreach ($renditions[$fmt] as $w => $url) {
                $pieces[] = $url . ' ' . $w . 'w';
            }
            $srcset[$fmt] = implode(', ', $pieces);
        }
    }
@endphp

@if ($hasRenditions)
    <picture>
        @if (isset($srcset['avif']))
            <source type="image/avif" srcset="{{ $srcset['avif'] }}" sizes="{{ $sizes }}" />
        @endif
        @if (isset($srcset['webp']))
            <source type="image/webp" srcset="{{ $srcset['webp'] }}" sizes="{{ $sizes }}" />
        @endif
        <img
            src="{{ $renditions['jpg'][array_key_first($renditions['jpg'])] ?? $src }}"
            @if (isset($srcset['jpg'])) srcset="{{ $srcset['jpg'] }}" sizes="{{ $sizes }}" @endif
            alt="{{ $alt }}"
            loading="{{ $loading }}"
            decoding="async"
            @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
            @if ($width) width="{{ $width }}" @endif
            @if ($height) height="{{ $height }}" @endif
            class="{{ $class }}"
        />
    </picture>
@else
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        loading="{{ $loading }}"
        decoding="async"
        @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
        @if ($width) width="{{ $width }}" @endif
        @if ($height) height="{{ $height }}" @endif
        class="{{ $class }}"
    />
@endif
