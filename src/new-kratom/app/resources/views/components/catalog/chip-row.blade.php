@props([
    'chips' => [],          // [['value' => 'all', 'label' => 'Vše'], …]
    'activeValue' => 'all',
    'withFilter' => false,  // V2: render «Filtry» button on the right that opens filter-modal
])

<div @class(['chip-row', 'chip-row--with-filter' => $withFilter])>
    <div class="container chip-row__inner">
        <div class="chip-row__scroll" role="tablist" aria-label="Rychlý filtr">
            @foreach($chips as $chip)
                <button
                    type="button"
                    role="tab"
                    class="chip"
                    :class="$store.catalog.chip === @js($chip['value']) && 'chip--active'"
                    :aria-selected="$store.catalog.chip === @js($chip['value'])"
                    x-on:click="$store.catalog.setChip(@js($chip['value']))"
                >
                    {{ $chip['label'] }}
                </button>
            @endforeach
        </div>

        @if($withFilter)
            <button
                type="button"
                class="chip-row__filter-btn"
                x-on:click="window.dispatchEvent(new CustomEvent('catalog:open-filter'))"
                aria-haspopup="dialog"
            >
                <x-ui.icon name="menu" :size="18" />
                <span>Filtry</span>
                <span class="chip-row__filter-count" x-show="$store.catalog.activeCount > 0" x-cloak x-text="$store.catalog.activeCount"></span>
            </button>
        @endif
    </div>
</div>
