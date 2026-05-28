{{-- S4 TrustBar — 04_HOMEPAGE.md §6 --}}
@php
    $items = [
        ['icon' => 'flask',        'label' => 'Akreditovaná lab. VŠCHT', 'href' => '/laboratorni-testy'],
        ['icon' => 'shield-check', 'label' => 'Licence MZ ČR (PML)',     'href' => '/licence'],
        ['icon' => 'store',        'label' => '2 prodejny v Praze',      'href' => '/prodejny'],
        ['icon' => 'zap',          'label' => 'EXPRESS 180 min',         'href' => '/doruceni'],
    ];
@endphp

<section class="trustbar" aria-label="Důvěryhodnost a benefity">
    <div class="container">
        <div class="trustbar__row">
            @foreach($items as $item)
                <a href="{{ $item['href'] }}" class="trustbar__item">
                    <span class="ico"><x-ui.icon :name="$item['icon']" /></span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
