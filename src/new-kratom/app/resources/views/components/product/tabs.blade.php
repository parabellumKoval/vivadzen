@props([
    'tabs' => [],   // [['id'=>'popis','label'=>'Popis','count'=>null], …]
])

<nav class="product-tabs" aria-label="Sekce produktu">
    <div class="container">
        <ul class="product-tabs__list" role="tablist">
            @foreach($tabs as $tab)
                <li class="product-tabs__item">
                    <a href="#{{ $tab['id'] }}" class="product-tabs__link" role="tab">
                        {{ $tab['label'] }}
                        @if(!empty($tab['count']))
                            <span class="product-tabs__count">({{ $tab['count'] }})</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</nav>
