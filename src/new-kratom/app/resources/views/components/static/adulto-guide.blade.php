@props([
    // 'full' — long-form step-by-step guide (used on /overeni-veku page).
    // 'short' — condensed 3-step summary (used inside the checkout modal).
    'version' => 'full',
    // When false, the "Important" + "Alternative" trailing sections are skipped
    // here so the page can render them separately (e.g. in a sticky sidebar).
    'showExtras' => true,
])

@php
    $isShort = $version === 'short';
    $prefix = $isShort ? 'short.' : '';
    $sectionImages = [
        '1' => [
            ['src' => '/images/adulto/1.jpg', 'w' => 1280, 'h' => 745],
        ],
        '2' => [
            ['src' => '/images/adulto/2.jpg', 'w' => 1166, 'h' => 1264],
        ],
        '3' => [
            ['src' => '/images/adulto/3.jpg', 'w' => 1280, 'h' => 727],
            ['src' => '/images/adulto/4.jpg', 'w' => 1280, 'h' => 1231],
        ],
        '4' => [
            ['src' => '/images/adulto/5.jpg', 'w' => 588, 'h' => 881],
            ['src' => '/images/adulto/6.jpg', 'w' => 515, 'h' => 1000],
        ],
    ];

    $intro = trans('site.adulto.'.$prefix.'intro');
    $sections = trans('site.adulto.'.$prefix.'sections');
@endphp

<div class="adulto-guide adulto-guide--{{ $version }}">
    <header class="adulto-guide__intro">
        @foreach($intro['paragraphs'] ?? [] as $paragraph)
            <p>{!! $paragraph !!}</p>
        @endforeach
        @if(!empty($intro['alert']))
            <div class="adulto-guide__alert">{!! $intro['alert'] !!}</div>
        @endif
    </header>

    @foreach($sections ?? [] as $section)
        @php
            $images = $sectionImages[$section['number']] ?? [];
            $imageCount = count($images);
            $imagesClass = $imageCount === 1
                ? 'adulto-guide__images--single'
                : ($section['number'] === '3' ? 'adulto-guide__images--stacked' : 'adulto-guide__images--pair');
        @endphp
        <section class="adulto-guide__section">
            <div class="adulto-guide__section-head">
                <span class="adulto-guide__section-num">{{ $section['number'] }}</span>
                <h2 class="adulto-guide__section-title">{!! $section['title'] !!}</h2>
            </div>

            @if(!empty($section['lead']))
                <p class="adulto-guide__lead">{!! $section['lead'] !!}</p>
            @endif

            @if(!empty($section['steps']))
                <ol class="adulto-guide__steps">
                    @foreach($section['steps'] as $step)
                        <li>{!! $step !!}</li>
                    @endforeach
                </ol>
            @endif

            @if($imageCount > 0)
                <div class="adulto-guide__images {{ $imagesClass }}">
                    @foreach($images as $i => $img)
                        <img
                            src="{{ asset($img['src']) }}"
                            alt="{{ strip_tags($section['title']) }} {{ $i + 1 }}"
                            width="{{ $img['w'] }}"
                            height="{{ $img['h'] }}"
                            loading="lazy"
                            class="adulto-guide__image"
                        />
                    @endforeach
                </div>
            @endif

            @if(!empty($section['note']))
                <p class="adulto-guide__note">{!! $section['note'] !!}</p>
            @endif

            @if(!empty($section['tips']))
                <div class="adulto-guide__tips">
                    @foreach($section['tips'] as $tip)
                        <p>{!! $tip !!}</p>
                    @endforeach
                </div>
            @endif
        </section>
    @endforeach

    @if($showExtras)
        <x-static.adulto-extras :version="$version" layout="inline" />
    @endif
</div>
