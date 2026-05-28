@php use App\Support\Locale; @endphp

@props([
    'eyebrow' => 'Vivadzen',
    'title' => 'Laboratorně testovaný kratom pod licencí MZ ČR',
    'text' => 'Každá šarže s plným COA. 2 prodejny v Praze. Express 180 min v Praze a Ostravě.',
    'primaryHref' => null,
    'primaryLabel' => null,
    'secondaryHref' => null,
    'secondaryLabel' => null,
])

@php
    $primaryHref ??= Locale::url('/kratom');
    $primaryLabel ??= 'Prohlédnout kratom';
    $secondaryHref ??= Locale::url('/laboratorni-testy');
    $secondaryLabel ??= 'Laboratorní testy';
@endphp

<section class="static-cta">
    <div class="container">
        <div class="static-cta__inner">
            <p class="static-cta__eyebrow">{{ $eyebrow }}</p>
            <h2 class="static-cta__title">{{ $title }}</h2>
            <p class="static-cta__text">{{ $text }}</p>
            <div class="static-cta__actions">
                <a href="{{ $primaryHref }}" class="btn btn--primary btn--lg">
                    {{ $primaryLabel }}
                    <span class="btn__icon"><x-ui.icon name="arrow-right" /></span>
                </a>
                <a href="{{ $secondaryHref }}" class="btn btn--secondary btn--lg">
                    {{ $secondaryLabel }}
                </a>
            </div>
        </div>
    </div>
</section>
