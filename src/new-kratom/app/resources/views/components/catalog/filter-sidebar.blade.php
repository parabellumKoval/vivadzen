@props([
    'groups' => [],   // [['title' => '...', 'open' => true, 'key' => 'color', 'options' => [['label'=>'Zelený','value'=>'zeleny','vein'=>'green','count'=>4], …]], …]
])

<aside class="filter-sidebar" aria-label="Filtry produktů">
    <header class="filter-sidebar__head">
        <h2 class="filter-sidebar__title">FILTRY</h2>
        <button type="button" class="filter-sidebar__reset" x-on:click="clear()">
            Vyčistit
        </button>
    </header>

    @foreach($groups as $group)
        <details class="filter-group" @if(!empty($group['open'])) open @endif>
            <summary class="filter-group__head">
                <span class="filter-group__title">{{ $group['title'] }}</span>
                <span class="filter-group__icon"><x-ui.icon name="chevron-down" :size="18" /></span>
            </summary>
            <div class="filter-group__body">
                @if(!empty($group['type']) && $group['type'] === 'range')
                    <div class="filter-range">
                        <div class="filter-range__labels">
                            <span>{{ $group['min'] }}</span>
                            <span>{{ $group['max'] }}</span>
                        </div>
                        <input type="range" class="filter-range__input" min="0" max="100" value="50" disabled aria-label="{{ $group['title'] }}">
                    </div>
                @else
                    <ul class="filter-options">
                        @foreach($group['options'] ?? [] as $opt)
                            @php
                                $key = $group['key'] ?? null;       // color|strain|form|null
                                $value = $opt['value'] ?? null;
                                $disabled = $opt['disabled'] ?? false;
                                $bindable = $key && $value && !$disabled;
                                $modelExpr = $bindable ? ($key === 'color' ? 'colors' : ($key === 'strain' ? 'strains' : ($key === 'form' ? 'forms' : null))) : null;
                            @endphp
                            <li class="filter-option">
                                <label class="check" @if($disabled) class="check check--disabled" @endif>
                                    @if($modelExpr)
                                        <input
                                            type="checkbox"
                                            value="{{ $value }}"
                                            x-model="{{ $modelExpr }}"
                                            x-on:change="recount()"
                                        >
                                    @else
                                        <input type="checkbox" @disabled($disabled)>
                                    @endif
                                    <span class="filter-option__label">
                                        @if(!empty($opt['vein']))
                                            <span class="vein-dot vein-dot--{{ $opt['vein'] }}"></span>
                                        @endif
                                        {{ $opt['label'] }}
                                    </span>
                                    <span class="filter-option__count">({{ $opt['count'] ?? 0 }})</span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </details>
    @endforeach
</aside>
