{{--
    Generic карусель карточек. Использование:

    <x-ui.carousel :desktop="3" :tablet="2" :mobile="1" prev-label="Předchozí" next-label="Další">
        @foreach($items as $i)
            <x-ui.product-card ... />
        @endforeach
    </x-ui.carousel>

    JS-логика (carousel.js) сама раскладывает детей трека и навешивает x-bind.
--}}
@props([
    'desktop' => 3,
    'tablet' => 2,
    'mobile' => 1,
    'prevLabel' => 'Předchozí',
    'nextLabel' => 'Další',
    'class' => '',
    'maxWidth' => null,
    'gap' => null,
    'showDots' => true,
])

@php
    $config = json_encode([
        'desktop' => (int) $desktop,
        'tablet' => (int) $tablet,
        'mobile' => (int) $mobile,
    ]);

    $styles = collect([
        $maxWidth ? '--carousel-max-w: ' . $maxWidth : null,
        $gap ? '--carousel-gap: ' . $gap : null,
    ])->filter()->implode('; ');
@endphp

<div
    class="carousel {{ $class }}"
    x-data="carousel({{ $config }})"
    @if($styles) style="{{ $styles }}" @endif
>
    <div class="carousel__viewport">
        <button type="button"
                class="carousel__nav-btn carousel__nav-btn--prev"
                @click="prev()"
                aria-label="{{ $prevLabel }}">
            <x-ui.icon name="chevron-down" :size="24" />
        </button>

        <div class="carousel__track" x-ref="track">
            {{ $slot }}
        </div>

        <button type="button"
                class="carousel__nav-btn carousel__nav-btn--next"
                @click="next()"
                aria-label="{{ $nextLabel }}">
            <x-ui.icon name="chevron-down" :size="24" />
        </button>
    </div>

    @if($showDots)
        <div class="carousel__dots" x-show="pageTotal > 1" x-cloak>
            <template x-for="page in pageTotal" :key="page">
                <button type="button"
                        class="carousel__dot-btn"
                        @click="goTo(page - 1)"
                        :aria-label="`Strana ${page}`"></button>
            </template>
        </div>
    @endif
</div>
