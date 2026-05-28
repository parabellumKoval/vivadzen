@props([
    'colors' => [],     // array<colorSlug, ['label','count','vein','href','active','comingSoon']>
    'strains' => [],    // array<strainSlug, ['label','origin','href','active','comingSoon']>
    'colorsTitle' => 'Podle barvy žilky',
    'colorsLead' => '4 kategorie podle profilu mitragyninu',
    'strainsTitle' => 'Podle odrůdy',
    'strainsLead' => '6 odrůd s vlastní genetikou',
])

<section class="section section--cream cross-link" aria-labelledby="cross-link-title">
    <div class="container">
        <h2 id="cross-link-title" class="sr-only">Procházejte podle</h2>

        @if(!empty($colors))
            <div class="cross-link__block">
                <p class="t-overline section-head__eyebrow--soft">PODLE BARVY ŽILKY</p>
                <h3 class="t-heading-lg t-on-light-accent">{{ $colorsTitle }}</h3>
                <p class="t-body-md t-on-light-2 mt-2">{{ $colorsLead }}</p>

                <div class="cross-link__colors">
                    @foreach($colors as $c)
                        @php($disabled = !empty($c['active']) || !empty($c['comingSoon']))
                        <a
                            href="{{ $disabled ? '#' : $c['href'] }}"
                            class="color-card @if(!empty($c['active'])) color-card--active @endif @if(!empty($c['comingSoon'])) color-card--coming @endif"
                            @if($disabled) aria-disabled="true" tabindex="-1" @endif
                        >
                            <span class="color-card__dot vein-dot vein-dot--{{ $c['vein'] ?? 'green' }}"></span>
                            <span class="color-card__label">{{ $c['label'] }}</span>
                            <span class="color-card__meta">
                                @if(!empty($c['comingSoon']))
                                    Připravujeme
                                @else
                                    {{ $c['count'] ?? 0 }} produktů
                                @endif
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if(!empty($strains))
            <div class="cross-link__block">
                <p class="t-overline section-head__eyebrow--soft">PODLE ODRŮDY</p>
                <h3 class="t-heading-lg t-on-light-accent">{{ $strainsTitle }}</h3>
                <p class="t-body-md t-on-light-2 mt-2">{{ $strainsLead }}</p>

                <div class="cross-link__strains">
                    @foreach($strains as $s)
                        @php($disabled = !empty($s['active']) || !empty($s['comingSoon']))
                        <a
                            href="{{ $disabled ? '#' : $s['href'] }}"
                            class="strain-card @if(!empty($s['active'])) strain-card--active @endif"
                            @if($disabled) aria-disabled="true" tabindex="-1" @endif
                        >
                            <span class="strain-card__icon" aria-hidden="true">
                                <x-ui.icon name="leaf" :size="32" />
                            </span>
                            <span class="strain-card__label">{{ $s['label'] }}</span>
                            <span class="strain-card__origin">{{ $s['origin'] ?? '' }}</span>
                            @if(!empty($s['comingSoon']))
                                <span class="strain-card__soon">Připravujeme</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
