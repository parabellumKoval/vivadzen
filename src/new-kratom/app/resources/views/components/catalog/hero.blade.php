@props([
    'eyebrow' => 'KATALOG · PML SORTIMENT',
    'eyebrowAccent' => 'grass',                  // grass | terra | amber | cream
    'title' => 'Kratom — prášek a extrakt',
    'lead' => 'Specializovaný sortiment licencovaného PML prodejce. Každá šarže testovaná v akreditované laboratoři.',
    'primaryHref' => '#grid',
    'primaryLabel' => 'Procházet všechny odrůdy',
    'secondaryHref' => null,
    'secondaryLabel' => 'Co je kratom?',
    'infoTitle' => 'NAŠE STANDARDY',
    'stats' => [],                               // [['label' => '...', 'value' => '...'], …]
])

@php
    $secondaryHref ??= \App\Support\Locale::url('/pruvodce');
@endphp

<section class="cat-hero cat-hero--{{ $eyebrowAccent }}" aria-labelledby="cat-hero-title">
    <div class="container cat-hero__grid">
        <div class="cat-hero__main">
            <p class="t-overline cat-hero__eyebrow">{{ $eyebrow }}</p>
            <h1 id="cat-hero-title" class="cat-hero__title t-display-lg">{{ $title }}</h1>
            <p class="cat-hero__lead t-body-lg">{{ $lead }}</p>

            <div class="cat-hero__ctas">
                <x-ui.button variant="primary" :href="$primaryHref" icon="arrow-right">
                    {{ $primaryLabel }}
                </x-ui.button>
                @if($secondaryLabel)
                    <x-ui.button variant="secondary" :href="$secondaryHref" icon="arrow-right">
                        {{ $secondaryLabel }}
                    </x-ui.button>
                @endif
            </div>
        </div>

        @if(!empty($stats))
            <aside class="cat-hero__card" aria-label="{{ $infoTitle }}">
                <p class="cat-hero__card-title">{{ $infoTitle }}</p>
                <dl class="cat-hero__stats">
                    @foreach($stats as $s)
                        <div class="cat-hero__stat">
                            <dt>{{ $s['label'] }}</dt>
                            <dd>{{ $s['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </aside>
        @endif
    </div>
</section>
