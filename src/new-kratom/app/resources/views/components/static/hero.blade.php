@props([
    'eyebrow' => null,
    'title' => '',
    'lead' => null,
    'icon' => null,
])

<section class="static-hero">
    <div class="container">
        <div class="static-hero__inner">
            @if($icon)
                <div class="static-hero__icon" aria-hidden="true">
                    <x-ui.icon :name="$icon" :size="40" />
                </div>
            @endif
            @if($eyebrow)
                <p class="static-hero__eyebrow">{{ $eyebrow }}</p>
            @endif
            <h1 class="static-hero__title">{{ $title }}</h1>
            @if($lead)
                <p class="static-hero__lead">{{ $lead }}</p>
            @endif
        </div>
    </div>
</section>
