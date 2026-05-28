@props([
    'eyebrow' => null,
    'eyebrowVariant' => null, // null | soft | grass
    'title' => null,
    'titleTag' => 'h2',
    'titleClass' => 't-heading-xl t-on-light-accent',
    'lead' => null,
    'center' => false,
])

@php
    $classes = collect(['section-head', $center ? 'section-head--center' : null])->filter()->implode(' ');
    $eyebrowClass = collect([
        't-overline',
        'section-head__eyebrow',
        $eyebrowVariant ? 'section-head__eyebrow--' . $eyebrowVariant : null,
    ])->filter()->implode(' ');
@endphp

<header class="{{ $classes }}">
    @if($eyebrow)
        <p class="{{ $eyebrowClass }}">{{ $eyebrow }}</p>
    @endif

    @if($title)
        <{{ $titleTag }} class="{{ $titleClass }}">{{ $title }}</{{ $titleTag }}>
    @endif

    @if($lead)
        <p class="section-head__lead t-body-md">{{ $lead }}</p>
    @endif

    {{ $slot ?? '' }}
</header>
