{{--
    DRAFT — pure-SVG verze "Návod k použití".
    Zachováno pro případnou budoucí variantu, kde nechceme rastrové obrázky.
    Aktivní komponenta používá ručně kreslené PNG ilustrace (../navod.blade.php).
--}}
@php
    $steps = [
        [
            'title' => 'Odměřte dávku',
            'text'  => 'Odměřte 3 g prášku — doporučená jednorázová dávka.',
            'illu'  => 'scoop',
        ],
        [
            'title' => 'Rozmíchejte ve vodě',
            'text'  => 'Vysypte do 100–250 ml vlažné vody a důkladně promíchejte.',
            'illu'  => 'glass',
        ],
        [
            'title' => 'Vypijte přípravek',
            'text'  => 'Maximální doporučená denní dávka činí 10 g.',
            'illu'  => 'drink',
        ],
        [
            'title' => 'Skladujte správně',
            'text'  => 'V suchu, chladu a mimo dosah dětí.',
            'illu'  => 'storage',
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
                        <div class="navod-step__illu">
                            @switch($step['illu'])

                                @case('scoop')
                                    {{-- Klasická čajová lžička s elongovanou miskou + hromádka prášku + štítek "3 g" --}}
                                    <svg viewBox="0 0 140 120" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Lžička s kratomovým práškem">
                                        {{-- stín pod lžičkou --}}
                                        <ellipse cx="56" cy="100" rx="42" ry="3.5" fill="var(--c-forest-700)" opacity="0.1"/>

                                        {{-- Lžička natočená o -18° (klasický 3/4 pohled) --}}
                                        <g transform="rotate(-18 56 66)">
                                            {{-- držadlo (dlouhé, štíhlé, mírně zúžené) --}}
                                            <path d="M76 60 L128 50 Q132 49 132 53 L132 57 Q132 61 128 62 L76 72 Z"
                                                  fill="var(--c-cream-200)" stroke="var(--c-forest-700)"
                                                  stroke-width="2" stroke-linejoin="round"/>
                                            {{-- jemná střední rýha držadla --}}
                                            <line x1="84" y1="61" x2="125" y2="56"
                                                  stroke="var(--c-forest-700)" stroke-width="0.8"
                                                  stroke-linecap="round" opacity="0.25"/>

                                            {{-- krk lžičky (přechod do misky) --}}
                                            <path d="M66 56 Q72 56 76 60 L76 72 Q72 76 66 76 Z"
                                                  fill="var(--c-cream-200)" stroke="var(--c-forest-700)"
                                                  stroke-width="2" stroke-linejoin="round"/>

                                            {{-- miska — klasický prodloužený oválný tvar (čajová lžička z mírného nadhledu) --}}
                                            <ellipse cx="34" cy="66" rx="34" ry="17"
                                                     fill="var(--c-cream-100)" stroke="var(--c-forest-700)" stroke-width="2"/>
                                            {{-- vnitřní prohlubeň --}}
                                            <ellipse cx="34" cy="64" rx="28" ry="12"
                                                     fill="none" stroke="var(--c-forest-700)"
                                                     stroke-width="0.8" opacity="0.35"/>

                                            {{-- hromádka kratomového prášku v misce --}}
                                            <path d="M8 60 Q14 44 34 42 Q54 44 60 60 Q54 70 34 70 Q14 70 8 60 Z"
                                                  fill="var(--c-grass-300)" stroke="var(--c-forest-700)"
                                                  stroke-width="2" stroke-linejoin="round"/>
                                            {{-- vrchní stín hromádky --}}
                                            <path d="M14 54 Q22 46 34 46 Q46 46 54 54"
                                                  fill="none" stroke="var(--c-grass-700)"
                                                  stroke-width="1.4" stroke-linecap="round" opacity="0.55"/>

                                            {{-- zrnka prášku --}}
                                            <circle cx="22" cy="52" r="1.2" fill="var(--c-forest-700)" opacity="0.7"/>
                                            <circle cx="36" cy="50" r="1.2" fill="var(--c-forest-700)" opacity="0.7"/>
                                            <circle cx="46" cy="54" r="1" fill="var(--c-forest-700)" opacity="0.6"/>
                                            <circle cx="30" cy="58" r="0.9" fill="var(--c-forest-700)" opacity="0.5"/>
                                            <circle cx="42" cy="46" r="0.8" fill="var(--c-forest-700)" opacity="0.5"/>
                                        </g>

                                        {{-- pár zrníček na povrchu (rozsypaný prášek) --}}
                                        <circle cx="22" cy="102" r="1.2" fill="var(--c-grass-700)" opacity="0.55"/>
                                        <circle cx="32" cy="106" r="1" fill="var(--c-grass-700)" opacity="0.45"/>
                                        <circle cx="86" cy="104" r="1.2" fill="var(--c-grass-700)" opacity="0.5"/>

                                        {{-- štítek 3 g (vpravo dole) --}}
                                        <g transform="translate(112 92)">
                                            <circle r="18" fill="var(--c-amber-500)" stroke="var(--c-forest-700)" stroke-width="2"/>
                                            <text x="0" y="2" text-anchor="middle" dominant-baseline="middle"
                                                  font-family="Inter, sans-serif" font-size="13" font-weight="700"
                                                  fill="var(--c-ink-900)">3 g</text>
                                        </g>
                                    </svg>
                                @break

                                @case('glass')
                                    {{-- Sklenice vody se lžičkou, vír míchání --}}
                                    <svg viewBox="0 0 140 120" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Sklenice s vodou a lžičkou">
                                        {{-- stín --}}
                                        <ellipse cx="68" cy="108" rx="32" ry="3" fill="var(--c-forest-700)" opacity="0.08"/>
                                        {{-- sklenice (lichoběžník) --}}
                                        <path d="M48 22 L92 22 L86 104 L54 104 Z"
                                              fill="var(--c-cream-100)" stroke="var(--c-forest-700)" stroke-width="2" stroke-linejoin="round"/>
                                        {{-- voda --}}
                                        <path d="M55 50 L85 50 L86 104 L54 104 Z" fill="var(--c-grass-300)" opacity="0.75"/>
                                        {{-- hladina --}}
                                        <path d="M55 50 Q63 46 70 50 T85 50" fill="none" stroke="var(--c-forest-700)" stroke-width="1.8" stroke-linecap="round"/>
                                        {{-- bublinky --}}
                                        <circle cx="62" cy="80" r="2" fill="var(--c-cream-100)" opacity="0.9"/>
                                        <circle cx="78" cy="72" r="1.5" fill="var(--c-cream-100)" opacity="0.9"/>
                                        <circle cx="72" cy="90" r="1.8" fill="var(--c-cream-100)" opacity="0.9"/>
                                        {{-- držadlo lžičky --}}
                                        <line x1="80" y1="12" x2="66" y2="78" stroke="var(--c-forest-700)" stroke-width="3" stroke-linecap="round"/>
                                        {{-- miska lžičky --}}
                                        <ellipse cx="64" cy="84" rx="6" ry="9" fill="var(--c-cream-200)"
                                                 stroke="var(--c-forest-700)" stroke-width="2"
                                                 transform="rotate(-12 64 84)"/>
                                        {{-- vír/proudění uvnitř vody (znázornění míchání) --}}
                                        <path d="M60 66 Q72 70 70 78 Q68 86 78 88"
                                              fill="none" stroke="var(--c-forest-700)" stroke-width="1.5"
                                              stroke-linecap="round" opacity="0.45"
                                              stroke-dasharray="3 3"/>
                                    </svg>
                                @break

                                @case('drink')
                                    {{-- Postava držící sklenici + slunce (1× denně, max 10 g) --}}
                                    <svg viewBox="0 0 140 120" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Denní dávka, maximálně 10 g">
                                        {{-- slunce v rohu (jednou denně) --}}
                                        <g transform="translate(112 24)" stroke="var(--c-amber-500)" stroke-width="2" stroke-linecap="round" fill="none">
                                            <circle r="8" fill="var(--c-amber-300)" stroke="var(--c-amber-700)"/>
                                            <line x1="0" y1="-14" x2="0" y2="-11"/>
                                            <line x1="0" y1="11" x2="0" y2="14"/>
                                            <line x1="-14" y1="0" x2="-11" y2="0"/>
                                            <line x1="11" y1="0" x2="14" y2="0"/>
                                            <line x1="-10" y1="-10" x2="-8" y2="-8"/>
                                            <line x1="8" y1="8" x2="10" y2="10"/>
                                            <line x1="-10" y1="10" x2="-8" y2="8"/>
                                            <line x1="8" y1="-8" x2="10" y2="-10"/>
                                        </g>
                                        {{-- stín pod postavou --}}
                                        <ellipse cx="60" cy="108" rx="38" ry="3" fill="var(--c-forest-700)" opacity="0.1"/>

                                        {{-- VLASY (kreslíme NEJDŘÍV — hlava jde nahoru) --}}
                                        {{-- Rámeček dlouhých vlasů kolem obličeje a po stranách dolů --}}
                                        <path d="M49 42
                                                 Q47 24 65 22
                                                 Q83 24 81 42
                                                 Q81 52 80 60
                                                 L77 68 L73 68
                                                 L72 58 Q71 55 68 55 L62 55 Q59 55 58 58 L57 68
                                                 L53 68 L50 60
                                                 Q49 52 49 42 Z"
                                              fill="var(--c-forest-700)"/>

                                        {{-- HLAVA / OBLIČEJ --}}
                                        <circle cx="65" cy="42" r="13.5" fill="var(--c-cream-200)" stroke="var(--c-forest-700)" stroke-width="2"/>
                                        {{-- Ofina (vlasy nad čelem) --}}
                                        <path d="M53 36 Q58 30 65 32 Q72 30 77 36"
                                              fill="none" stroke="var(--c-forest-700)" stroke-width="2.4"
                                              stroke-linecap="round"/>
                                        {{-- Oči --}}
                                        <circle cx="60" cy="42" r="1.2" fill="var(--c-forest-700)"/>
                                        <circle cx="70" cy="42" r="1.2" fill="var(--c-forest-700)"/>
                                        {{-- Úsměv --}}
                                        <path d="M59 47 Q65 51 71 47" fill="none" stroke="var(--c-forest-700)" stroke-width="1.6" stroke-linecap="round"/>

                                        {{-- TĚLO — košile / halenka (obrysová, ne plná barva) --}}
                                        <path d="M42 108
                                                 L42 80
                                                 Q42 64 56 60
                                                 L74 60
                                                 Q88 64 88 80
                                                 L88 108"
                                              fill="var(--c-cream-200)" stroke="var(--c-forest-700)"
                                              stroke-width="2" stroke-linejoin="round"/>
                                        {{-- výstřih --}}
                                        <path d="M58 60 Q65 67 72 60" fill="none" stroke="var(--c-forest-700)" stroke-width="1.6" stroke-linecap="round"/>
                                        {{-- jemný švov v pase --}}
                                        <line x1="46" y1="90" x2="84" y2="90" stroke="var(--c-forest-700)" stroke-width="0.8" opacity="0.25"/>

                                        {{-- PAŽE držící sklenici (obrysová) --}}
                                        <path d="M84 78
                                                 Q92 72 99 76
                                                 L101 84
                                                 Q95 80 87 84 Z"
                                              fill="var(--c-cream-200)" stroke="var(--c-forest-700)"
                                              stroke-width="1.8" stroke-linejoin="round"/>

                                        {{-- SKLENICE v ruce --}}
                                        <path d="M93 70 L107 70 L105.5 90 L94.5 90 Z"
                                              fill="var(--c-cream-100)" stroke="var(--c-forest-700)" stroke-width="2" stroke-linejoin="round"/>
                                        <path d="M94.2 76 L105.8 76 L105.5 90 L94.5 90 Z" fill="var(--c-grass-300)" opacity="0.85"/>
                                        <path d="M94.2 76 Q97 74 100 76 T105.8 76" fill="none" stroke="var(--c-forest-700)" stroke-width="1.4" stroke-linecap="round"/>
                                        {{-- štítek "MAX 10 g" --}}
                                        <g transform="translate(34 96)">
                                            <rect x="-30" y="-11" width="60" height="22" rx="11"
                                                  fill="var(--c-cream-100)" stroke="var(--c-forest-700)" stroke-width="2"/>
                                            <text x="0" y="1" text-anchor="middle" dominant-baseline="middle"
                                                  font-family="Inter, sans-serif" font-size="11" font-weight="700"
                                                  fill="var(--c-terracotta-500)" letter-spacing="0.3">MAX 10 g</text>
                                        </g>
                                    </svg>
                                @break

                                @case('storage')
                                    {{-- Doypack na poličce + sněhová vločka (chlad) + dítě (zákaz) --}}
                                    <svg viewBox="0 0 140 120" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Skladujte v suchu a chladu, mimo dosah dětí">
                                        {{-- polička --}}
                                        <rect x="18" y="102" width="104" height="6" rx="1"
                                              fill="var(--c-forest-700)"/>
                                        <rect x="18" y="102" width="104" height="2" fill="var(--c-cream-100)" opacity="0.25"/>

                                        {{-- doypack — stand-up pouch s vrchním zipem --}}
                                        {{-- horní zip / sealovaná část --}}
                                        <path d="M56 28 L84 28"
                                              stroke="var(--c-forest-700)" stroke-width="2" stroke-linecap="round"/>
                                        {{-- tělo doypacku (zaoblené rohy nahoře, mírně širší dolu pro stabilitu) --}}
                                        <path d="M56 28
                                                 L84 28
                                                 Q90 28 92 34
                                                 L96 92
                                                 Q98 102 88 102
                                                 L52 102
                                                 Q42 102 44 92
                                                 L48 34
                                                 Q50 28 56 28 Z"
                                              fill="var(--c-cream-200)" stroke="var(--c-forest-700)"
                                              stroke-width="2" stroke-linejoin="round"/>
                                        {{-- horní pruh (sealed seam) --}}
                                        <path d="M50 36 Q70 33 90 36"
                                              fill="none" stroke="var(--c-forest-700)"
                                              stroke-width="1" opacity="0.35" stroke-dasharray="2 2"/>
                                        {{-- spodní gusset (stojací dno) --}}
                                        <path d="M48 96 Q70 100 92 96"
                                              fill="none" stroke="var(--c-forest-700)"
                                              stroke-width="1" opacity="0.35"/>
                                        {{-- štítek na doypacku --}}
                                        <rect x="56" y="52" width="28" height="34" rx="2"
                                              fill="var(--c-grass-300)" stroke="var(--c-forest-700)" stroke-width="1.8"/>
                                        <line x1="61" y1="62" x2="79" y2="62" stroke="var(--c-forest-700)" stroke-width="1.6" stroke-linecap="round"/>
                                        <line x1="61" y1="70" x2="75" y2="70" stroke="var(--c-forest-700)" stroke-width="1.4" stroke-linecap="round" opacity="0.55"/>
                                        <line x1="61" y1="76" x2="77" y2="76" stroke="var(--c-forest-700)" stroke-width="1.4" stroke-linecap="round" opacity="0.55"/>

                                        {{-- sněhová vločka (chlad) — posunuta níže aby ji nepřekrývala čísélka --}}
                                        <g transform="translate(22 70)" stroke-linecap="round" fill="none">
                                            <circle r="14" fill="var(--c-cream-100)" stroke="var(--c-forest-700)" stroke-width="2"/>
                                            <g stroke="var(--c-info)" stroke-width="2">
                                                <line x1="0" y1="-8" x2="0" y2="8"/>
                                                <line x1="-8" y1="0" x2="8" y2="0"/>
                                                <line x1="-5.7" y1="-5.7" x2="5.7" y2="5.7"/>
                                                <line x1="-5.7" y1="5.7" x2="5.7" y2="-5.7"/>
                                                <path d="M-2 -8 L0 -6 L2 -8"/>
                                                <path d="M-2 8 L0 6 L2 8"/>
                                                <path d="M-8 -2 L-6 0 L-8 2"/>
                                                <path d="M8 -2 L6 0 L8 2"/>
                                            </g>
                                        </g>

                                        {{-- ikona "mimo dosah dětí" — pravý horní roh (výš než sněhová vločka pro vizuální rytmus) --}}
                                        <g transform="translate(116 46)">
                                            <circle r="14" fill="var(--c-cream-100)" stroke="var(--c-forest-700)" stroke-width="2"/>
                                            {{-- hlavička dítěte --}}
                                            <circle cx="0" cy="-4" r="3" fill="var(--c-forest-700)"/>
                                            {{-- tělíčko --}}
                                            <path d="M-5 6 Q-4 0 0 0 Q4 0 5 6 Z" fill="var(--c-forest-700)"/>
                                            {{-- zákazové přeškrtnutí --}}
                                            <line x1="-10" y1="10" x2="10" y2="-10"
                                                  stroke="var(--c-terracotta-500)" stroke-width="3" stroke-linecap="round"/>
                                        </g>
                                    </svg>
                                @break

                            @endswitch
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
