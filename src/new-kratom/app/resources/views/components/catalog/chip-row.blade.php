@props([
    'chips' => [],          // [['value' => 'all', 'label' => 'Vše'], …]
    'activeValue' => 'all',
])

<div class="chip-row">
    <div class="container chip-row__inner">
        <div class="chip-row__scroll" role="tablist" aria-label="Rychlý filtr">
            @foreach($chips as $chip)
                <button
                    type="button"
                    role="tab"
                    class="chip"
                    :class="chip === @js($chip['value']) && 'chip--active'"
                    :aria-selected="chip === @js($chip['value'])"
                    x-on:click="setChip(@js($chip['value']))"
                >
                    {{ $chip['label'] }}
                </button>
            @endforeach
        </div>
    </div>
</div>
