@php
    use App\Support\Locale;

    $allTests = collect($batch['tests'])->flatten(1);
    $totalTests = 0;
    $passedTests = 0;
    foreach ($batch['tests'] as $group) {
        foreach ($group as $row) {
            $totalTests++;
            if (($row['status'] ?? '') === 'V') $passedTests++;
        }
    }
    $passRatio = $totalTests > 0 ? ($passedTests / $totalTests) : 0;

    // Headline metrics — pick the most consumer-relevant values.
    $mitragynin = collect($batch['tests']['active'] ?? [])->firstWhere('name', 'Mitragynin');
    $sevenOH   = collect($batch['tests']['active'] ?? [])->firstWhere('name', '7-hydroxymitragynin');

    $categoryMeta = [
        'active' => [
            'icon'  => 'sparkles',
            'title' => 'Aktivní látky',
            'lead'  => 'Mitragynin a 7-OH — hlavní alkaloidy kratomu. Stanoveno metodou LC-MS dle KM 20.',
            'spec'  => 'Příloha č. 1 Vyhlášky č. 448/2025 Sb.',
        ],
        'metals' => [
            'icon'  => 'shield-check',
            'title' => 'Těžké kovy',
            'lead'  => 'Rtuť, olovo, kadmium, arsen, nikl. Stanoveno metodami AAS-AMA a ICP-MS.',
            'spec'  => 'Příloha č. 3 Vyhlášky č. 448/2025 Sb.',
        ],
        'mycotoxins' => [
            'icon'  => 'leaf',
            'title' => 'Mykotoxiny',
            'lead'  => 'Aflatoxiny B1, B2, G1, G2 — všechny pod mezí stanovitelnosti.',
            'spec'  => 'Metoda KM 06 (LC-MS/MS).',
        ],
        'pah' => [
            'icon'  => 'flask',
            'title' => 'Polyaromatické uhlovodíky',
            'lead'  => 'Suma 4 PAU dle EU. Stanoveno metodou HPLC-FLD (KM 08A).',
            'spec'  => 'Příloha č. 3 Vyhlášky č. 448/2025 Sb.',
        ],
        'microbiology' => [
            'icon'  => 'badge-check',
            'title' => 'Mikrobiologie',
            'lead'  => 'CPM, kvasinky, plísně, E. coli, Salmonella. Dle ČSN EN ISO.',
            'spec'  => 'ZL ÚBM VŠCHT Praha — akreditace č. 1316.3.',
        ],
    ];

    $statusBadge = function (string $status) {
        return match ($status) {
            'V'  => ['label' => 'PASS', 'class' => 'pass'],
            'Vn' => ['label' => 'PASS', 'class' => 'pass'],
            'N'  => ['label' => 'FAIL', 'class' => 'fail'],
            'X'  => ['label' => 'INFO', 'class' => 'info'],
            default => ['label' => '—', 'class' => 'info'],
        };
    };

    $formatNum = function ($v) {
        if (!is_numeric($v)) return $v;
        if ($v == 0) return '0';
        if (abs($v) < 0.01) return rtrim(rtrim(number_format($v, 4, ',', ' '), '0'), ',');
        if (abs($v) >= 1000) return number_format($v, 0, ',', ' ');
        return rtrim(rtrim(number_format($v, 3, ',', ' '), '0'), ',');
    };
@endphp

<x-layouts.app
    title="Šarže {{ $batchId }} — Lab COA | Vivadzen"
    description="Ověření šarže {{ $batchId }} ({{ $batch['product_name'] }}) v akreditované laboratoři VŠCHT Praha dle ISO/IEC 17025."
>
    {{-- ============================================================ --}}
    {{-- Hero — verification banner --}}
    {{-- ============================================================ --}}
    <section class="lab-batch-hero" data-anim>
        <div class="lab-batch-hero__bg" aria-hidden="true">
            <div class="lab-batch-hero__orb lab-batch-hero__orb--1"></div>
            <div class="lab-batch-hero__orb lab-batch-hero__orb--2"></div>
            <svg class="lab-batch-hero__grid" viewBox="0 0 800 400" preserveAspectRatio="none" aria-hidden="true">
                <defs>
                    <pattern id="grid-pat" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M40 0H0V40" fill="none" stroke="rgba(126,200,85,0.08)" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect width="800" height="400" fill="url(#grid-pat)"/>
            </svg>
        </div>

        <div class="container lab-batch-hero__inner">

            <div class="lab-batch-hero__seal" aria-hidden="true">
                <svg viewBox="0 0 120 120" class="lab-batch-hero__seal-svg">
                    <circle class="lab-batch-hero__seal-ring" cx="60" cy="60" r="56" />
                    <circle class="lab-batch-hero__seal-pulse" cx="60" cy="60" r="56" />
                    <path class="lab-batch-hero__seal-check" d="M40 62 L54 76 L82 46" />
                </svg>
            </div>

            <p class="lab-batch-hero__eyebrow">
                <span class="lab-batch-hero__dot"></span>
                Ověřeno · VŠCHT Praha · ISO/IEC 17025
            </p>

            <h1 class="lab-batch-hero__title">
                Šarže <span class="lab-batch-hero__lot">{{ $batchId }}</span> je platná
            </h1>

            <p class="lab-batch-hero__lead">
                {{ $batch['product_name'] }} prošel kompletním panelem laboratorních zkoušek
                v akreditované laboratoři VŠCHT Praha. Všechny regulované parametry odpovídají
                limitům Vyhlášky č. 448/2025 Sb.
            </p>

            <dl class="lab-batch-hero__meta">
                <div>
                    <dt>Produkt</dt>
                    <dd>{{ $batch['product_name'] }}</dd>
                </div>
                <div>
                    <dt>Odběr</dt>
                    <dd>{{ \Carbon\Carbon::parse($batch['received_at'])->translatedFormat('d. m. Y') }}</dd>
                </div>
                <div>
                    <dt>Protokol vystaven</dt>
                    <dd>{{ \Carbon\Carbon::parse($batch['issued_at'])->translatedFormat('d. m. Y') }}</dd>
                </div>
                <div>
                    <dt>Obal</dt>
                    <dd>{{ $batch['mass'] }} · {{ $batch['package'] }}</dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Summary — headline metrics with circular gauges --}}
    {{-- ============================================================ --}}
    <section class="lab-batch-summary">
        <div class="container">
            <div class="lab-batch-summary__grid">

                {{-- Overall pass ratio --}}
                <article class="lab-gauge lab-gauge--big" data-anim style="--anim-delay: 0ms;">
                    <div class="lab-gauge__chart">
                        <svg viewBox="0 0 160 160" aria-hidden="true">
                            <circle class="lab-gauge__track" cx="80" cy="80" r="68" />
                            <circle
                                class="lab-gauge__fill lab-gauge__fill--success"
                                cx="80" cy="80" r="68"
                                style="--ratio: {{ $passRatio }};"
                            />
                        </svg>
                        <div class="lab-gauge__value">
                            <span class="lab-gauge__num" data-count-to="{{ round($passRatio * 100) }}">0</span><span class="lab-gauge__unit">%</span>
                        </div>
                    </div>
                    <h3 class="lab-gauge__title">Hodnocených parametrů prošlo</h3>
                    <p class="lab-gauge__sub">{{ $passedTests }}&nbsp;/&nbsp;{{ $totalTests }} testů</p>
                </article>

                @if($mitragynin)
                    @php
                        $mRatio = min($mitragynin['value'] / $mitragynin['limit'], 1);
                    @endphp
                    <article class="lab-gauge" data-anim style="--anim-delay: 120ms;">
                        <div class="lab-gauge__chart">
                            <svg viewBox="0 0 160 160" aria-hidden="true">
                                <circle class="lab-gauge__track" cx="80" cy="80" r="68" />
                                <circle
                                    class="lab-gauge__fill lab-gauge__fill--accent"
                                    cx="80" cy="80" r="68"
                                    style="--ratio: {{ $mRatio }};"
                                />
                            </svg>
                            <div class="lab-gauge__value">
                                <span class="lab-gauge__num" data-count-to="{{ $mitragynin['value'] }}" data-decimals="2">0</span><span class="lab-gauge__unit">%</span>
                            </div>
                        </div>
                        <h3 class="lab-gauge__title">Mitragynin</h3>
                        <p class="lab-gauge__sub">limit {{ $formatNum($mitragynin['limit']) }} % · {{ round($mRatio * 100) }} % limitu</p>
                    </article>
                @endif

                @if($sevenOH)
                    @php
                        $sRatio = min($sevenOH['value'] / $sevenOH['limit'], 1);
                    @endphp
                    <article class="lab-gauge" data-anim style="--anim-delay: 240ms;">
                        <div class="lab-gauge__chart">
                            <svg viewBox="0 0 160 160" aria-hidden="true">
                                <circle class="lab-gauge__track" cx="80" cy="80" r="68" />
                                <circle
                                    class="lab-gauge__fill lab-gauge__fill--accent2"
                                    cx="80" cy="80" r="68"
                                    style="--ratio: {{ $sRatio }};"
                                />
                            </svg>
                            <div class="lab-gauge__value">
                                <span class="lab-gauge__num" data-count-to="{{ $sevenOH['value'] }}" data-decimals="4">0</span><span class="lab-gauge__unit">%</span>
                            </div>
                        </div>
                        <h3 class="lab-gauge__title">7-OH-mitragynin</h3>
                        <p class="lab-gauge__sub">limit {{ $formatNum($sevenOH['limit']) }} % · {{ round($sRatio * 100) }} % limitu</p>
                    </article>
                @endif

                <article class="lab-gauge lab-gauge--card" data-anim style="--anim-delay: 360ms;">
                    <div class="lab-gauge__badge">
                        <x-ui.icon name="shield-check" :size="32" />
                    </div>
                    <h3 class="lab-gauge__title">Akreditace</h3>
                    <p class="lab-gauge__sub">
                        VŠCHT Praha · ČIA č. 1316.2 a 1316.3<br>
                        ČSN EN ISO/IEC 17025:2018
                    </p>
                </article>

            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Test categories — animated bar charts --}}
    {{-- ============================================================ --}}
    @foreach($batch['tests'] as $key => $rows)
        @php $meta = $categoryMeta[$key] ?? null; @endphp
        @if(!$meta || empty($rows)) @continue @endif

        <section class="lab-batch-block" data-anim>
            <div class="container">
                <header class="lab-batch-block__head">
                    <div class="lab-batch-block__icon">
                        <x-ui.icon :name="$meta['icon']" :size="28" />
                    </div>
                    <div class="lab-batch-block__head-text">
                        <p class="lab-batch-block__eyebrow">{{ $meta['spec'] }}</p>
                        <h2 class="lab-batch-block__title">{{ $meta['title'] }}</h2>
                        <p class="lab-batch-block__lead">{{ $meta['lead'] }}</p>
                    </div>
                </header>

                <ul class="lab-bar-list">
                    @foreach($rows as $row)
                        @php
                            $b = $statusBadge($row['status'] ?? 'X');
                            $isNumeric = is_numeric($row['value']);
                            $hasLimit = isset($row['limit']) && is_numeric($row['limit']);
                            $ratio = ($isNumeric && $hasLimit && $row['limit'] > 0)
                                ? min($row['value'] / $row['limit'], 1.05)
                                : null;
                            $belowLoq = !empty($row['below_loq']);
                        @endphp

                        <li class="lab-bar">
                            <div class="lab-bar__name">
                                @if(!empty($row['symbol']))
                                    <span class="lab-bar__symbol">{{ $row['symbol'] }}</span>
                                @endif
                                <span>{{ $row['name'] }}</span>
                            </div>

                            <div class="lab-bar__measured">
                                @if($belowLoq)<span class="lab-bar__lt">&lt;</span>@endif{{ $formatNum($row['value']) }}
                                <span class="lab-bar__unit">{{ $row['unit'] }}</span>
                                @if(!empty($row['uncertainty']))
                                    <span class="lab-bar__uncertainty">± {{ $formatNum($row['uncertainty']) }}</span>
                                @endif
                            </div>

                            @if($ratio !== null)
                                <div class="lab-bar__track" aria-hidden="true">
                                    <div class="lab-bar__fill lab-bar__fill--{{ $b['class'] }}" style="--ratio: {{ $ratio }};"></div>
                                    <div class="lab-bar__marker" style="left: 100%;"></div>
                                </div>
                            @else
                                <div class="lab-bar__track lab-bar__track--flat" aria-hidden="true">
                                    <div class="lab-bar__fill lab-bar__fill--{{ $b['class'] }}" style="--ratio: 1;"></div>
                                </div>
                            @endif

                            @if($hasLimit)
                                <div class="lab-bar__limit">limit {{ $formatNum($row['limit']) }} {{ $row['unit'] }}</div>
                            @elseif(is_string($row['limit'] ?? null))
                                <div class="lab-bar__limit">limit · {{ $row['limit'] }}</div>
                            @else
                                <div class="lab-bar__limit">bez limitu</div>
                            @endif

                            <span class="lab-bar__badge lab-bar__badge--{{ $b['class'] }}">
                                @if($b['class'] === 'pass')
                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                @endif
                                {{ $b['label'] }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endforeach

    {{-- ============================================================ --}}
    {{-- Protocols — PDF download links --}}
    {{-- ============================================================ --}}
    <section class="lab-batch-protocols" data-anim>
        <div class="container">
            <header class="lab-batch-block__head lab-batch-block__head--center">
                <p class="lab-batch-block__eyebrow">Originální protokoly</p>
                <h2 class="lab-batch-block__title">Plné COA ke stažení</h2>
                <p class="lab-batch-block__lead">PDF protokoly podepsané vedoucími akreditovaných laboratoří VŠCHT.</p>
            </header>

            <div class="lab-protocol-grid">
                @foreach($batch['protocols'] as $i => $proto)
                    <a
                        href="{{ asset($proto['file']) }}"
                        target="_blank"
                        rel="noopener"
                        class="lab-protocol-card"
                        data-anim style="--anim-delay: {{ $i * 100 }}ms;"
                    >
                        <div class="lab-protocol-card__icon">
                            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="9" x2="15" y1="13" y2="13"/>
                                <line x1="9" x2="15" y1="17" y2="17"/>
                            </svg>
                        </div>
                        <div class="lab-protocol-card__body">
                            <p class="lab-protocol-card__no">Protokol č. {{ $proto['no'] }}</p>
                            <h3 class="lab-protocol-card__title">{{ $proto['label'] }}</h3>
                            <p class="lab-protocol-card__date">{{ \Carbon\Carbon::parse($proto['date'])->translatedFormat('d. m. Y') }}</p>
                        </div>
                        <span class="lab-protocol-card__cta" aria-hidden="true">
                            <x-ui.icon name="arrow-right" :size="20" />
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Trust strip --}}
    {{-- ============================================================ --}}
    <section class="lab-batch-trust">
        <div class="container">
            <div class="lab-batch-trust__inner">
                <div class="lab-batch-trust__col">
                    <x-ui.icon name="badge-check" :size="32" />
                    <h3>Akreditovaná laboratoř</h3>
                    <p>VŠCHT Praha — zkušební laboratoř č. 1316.2 a 1316.3 dle ČSN EN ISO/IEC 17025:2018.</p>
                </div>
                <div class="lab-batch-trust__col">
                    <x-ui.icon name="shield-check" :size="32" />
                    <h3>Vyhláška č. 448/2025 Sb.</h3>
                    <p>Hodnocení dle českých limitů pro psychomodulační látky — přílohy č. 1 a 3.</p>
                </div>
                <div class="lab-batch-trust__col">
                    <x-ui.icon name="leaf" :size="32" />
                    <h3>Sledovatelnost</h3>
                    <p>Každá šarže má unikátní lot, který si můžete kdykoliv ověřit na vivadzen.cz/sarze/&lt;lot&gt;.</p>
                </div>
            </div>
        </div>
    </section>

    <x-static.cta
        title="Vyberte si další laboratorně ověřenou šarži"
        text="Každý produkt na Vivadzen má COA podepsané VŠCHT Praha."
        :primaryLabel="'Prohlédnout kratom'"
        :secondaryLabel="'O laboratorních testech'"
        :secondaryHref="Locale::url('/laboratorni-testy')"
    />

    @push('head')
        <style>
            /* Inline critical so the hero seal animates on first paint. */
            [data-anim] { opacity: 0; transform: translateY(20px); }
            .anim-in [data-anim], [data-anim].is-in { opacity: 1; transform: none; }
        </style>
    @endpush
</x-layouts.app>
