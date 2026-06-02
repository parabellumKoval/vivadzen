@props([
    'version' => 'full',
    // 'inline' — rendered inside the main column (used inside the modal).
    // 'aside'  — rendered in the sticky sidebar on /overeni-veku.
    'layout' => 'aside',
])

@php
    $prefix = $version === 'short' ? 'short.' : '';
    $important = trans('site.adulto.'.$prefix.'important');
    $alternative = trans('site.adulto.'.$prefix.'alternative');
    $isAside = $layout === 'aside';
@endphp

<aside class="adulto-extras adulto-extras--{{ $layout }}">
    <section class="adulto-extras__card adulto-extras__card--accent">
        <span class="adulto-extras__eyebrow">{{ __('site.adulto.eyebrow') }}</span>
        <h2 class="adulto-extras__title">{!! $important['title'] !!}</h2>
        @foreach($important['paragraphs'] ?? [] as $paragraph)
            <p class="adulto-extras__text">{!! $paragraph !!}</p>
        @endforeach
        @if(!empty($important['steps']))
            <ol class="adulto-extras__steps">
                @foreach($important['steps'] as $step)
                    <li>{!! $step !!}</li>
                @endforeach
            </ol>
        @endif
        @if(!empty($important['footer']))
            <p class="adulto-extras__footer">{!! $important['footer'] !!}</p>
        @endif
    </section>

    <section class="adulto-extras__card">
        <h2 class="adulto-extras__title">{!! $alternative['title'] !!}</h2>
        @if(!empty($alternative['text']))
            <p class="adulto-extras__text">{!! $alternative['text'] !!}</p>
        @endif
        @if(!empty($alternative['bullets']))
            <ul class="adulto-extras__bullets">
                @foreach($alternative['bullets'] as $bullet)
                    <li>{!! $bullet !!}</li>
                @endforeach
            </ul>
        @endif
    </section>
</aside>
