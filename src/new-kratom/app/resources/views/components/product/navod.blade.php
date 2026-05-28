@php
    $steps = [
        [
            'title' => 'Odměřte dávku',
            'text'  => 'Odměřte 3 g prášku — doporučená jednorázová dávka.',
            'image' => 'assets/instruction/step-1.png',
            'alt'   => 'Měřicí lžička se 3 g kratomového prášku',
        ],
        [
            'title' => 'Rozmíchejte ve vodě',
            'text'  => 'Vysypte do 100–250 ml vlažné vody a důkladně promíchejte.',
            'image' => 'assets/instruction/step-2.png',
            'alt'   => 'Sklenice vody s lžičkou, šipka znázorňující míchání',
            'compact' => true,
        ],
        [
            'title' => 'Vypijte přípravek',
            'text'  => 'Maximální doporučená denní dávka činí 10 g.',
            'image' => 'assets/instruction/step-3.png',
            'alt'   => 'Žena pije ze sklenice, štítek MAX 10 g',
        ],
        [
            'title' => 'Skladujte správně',
            'text'  => 'V suchu, chladu a mimo dosah dětí.',
            'image' => 'assets/instruction/step4.png',
            'alt'   => 'Doypack na poličce, vedle sněhová vločka a piktogram „mimo dosah dětí"',
        ],
    ];
@endphp

<section class="section section--cream-50 navod-section" id="navod" aria-labelledby="navod-title">
    <div class="container">
        <header class="navod-section__header">
            <span class="t-overline navod-section__eyebrow">Krok za krokem</span>
            <h2 id="navod-title" class="t-heading-xl t-on-light-accent">Návod k použití</h2>
            <p class="navod-section__lead t-body-md">Čtyři jednoduché kroky pro správnou přípravu, dávkování a uchování.</p>
        </header>

        <ol class="navod-steps">
            @foreach($steps as $i => $step)
                <li class="navod-step">
                    <div class="navod-step__media">
                        <span class="navod-step__num" aria-hidden="true">{{ $i + 1 }}</span>
                        <div @class(['navod-step__illu', 'navod-step__illu--compact' => $step['compact'] ?? false])>
                            <img
                                src="{{ asset($step['image']) }}"
                                alt="{{ $step['alt'] }}"
                                loading="lazy"
                                decoding="async"
                                width="320"
                                height="320"
                            />
                        </div>
                    </div>
                    <h3 class="navod-step__title t-heading-sm">{{ $step['title'] }}</h3>
                    <p class="navod-step__text t-body-sm">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>

        <aside class="navod__note">
            <span class="navod__note-icon"><x-ui.icon name="shield-check" :size="20" /></span>
            <p>
                Postupujte výhradně dle uvedeného návodu. Nepřekračujte doporučenou denní dávku.
                Tento výrobek by se neměl užívat každý den — mezi jednotlivými užitími by měla být přestávka 3 dny.
            </p>
        </aside>
    </div>
</section>
