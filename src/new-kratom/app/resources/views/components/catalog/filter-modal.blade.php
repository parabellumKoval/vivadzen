@props([
    'groups' => [],
    'total' => 0,
])

@php
    // Pre-compute a map: group key → array of tag options. We classify each
    // group by `key` so the modal can render the right control variant
    // (image tags for colors, pill tags for strains/forms/availability,
    // dual range slider for numeric ranges like mitragynin / price).

    $colorImages = [
        'zeleny'  => asset('assets/categories/green.png'),
        'bily'    => asset('assets/categories/white.png'),
        'cerveny' => asset('assets/categories/red.png'),
        'zluty'   => asset('assets/categories/gold.png'),
    ];

    // Map availability/in-stock labels to dot classes
    $availabilityDots = [
        'Skladem'         => 'dot--ok',
        'Skladem v Praze' => 'dot--ok',
        'Připravujeme'    => 'dot--soon',
        'Předobjednávka'  => 'dot--soon',
    ];
@endphp

{{-- The page is responsible for placing this component via @push('modals')
     so it renders at the body level (escaping <main>'s isolate stacking
     context). The modal needs to sit above the sticky header (z:50);
     teleporting via Alpine had reactivity issues with x-show on descendants,
     so we render directly at body via Blade stacks. Filter STATE is shared
     through Alpine.store('catalog'); modal OPEN/CLOSE is local. --}}
<div
    x-data="filterModal"
    x-cloak
    x-show="open"
    x-transition.opacity
    class="filter-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="filter-modal-title"
    @keydown.escape.window="closeModal()"
>
    {{-- Only the OUTER element has x-show / x-transition. Inner panel slide-in
         is driven by a CSS @keyframes on `.filter-modal__panel` so we avoid
         Alpine's x-teleport descendant-reactivity quirk where x-show on a
         child no longer tracks the parent x-data after teleport. --}}
    <div class="filter-modal__backdrop" @click="closeModal()"></div>

    <aside class="filter-modal__panel">
        <header class="filter-modal__head">
            <h2 id="filter-modal-title" class="filter-modal__title">Filtry</h2>
            <button type="button" class="filter-modal__close" @click="closeModal()" aria-label="Zavřít">
                <x-ui.icon name="x" :size="20" />
            </button>
        </header>

        <div class="filter-modal__body">
            @foreach($groups as $group)
                @php
                    $key   = $group['key'] ?? null;
                    $type  = $group['type'] ?? null;
                    $title = $group['title'];
                    $stateKey = match ($key) {
                        'color'        => 'colors',
                        'strain'       => 'strains',
                        'form'         => 'forms',
                        'availability' => 'availability',
                        default        => null,
                    };
                @endphp

                <section class="filter-section">
                    <h3 class="filter-section__title">{{ mb_strtoupper($title) }}</h3>

                    @if($type === 'range')
                        @php
                            $min  = $group['rangeMinValue'] ?? 1.0;
                            $max  = $group['rangeMaxValue'] ?? 12.0;
                            $step = $group['rangeStep'] ?? 0.05;
                            $unit = $group['rangeUnit'] ?? ' %';
                        @endphp
                        <div class="range-dual">
                            <div
                                class="range-dual__track"
                                :style="`--min-ratio: ${Math.max(0, Math.min(1, ($store.catalog.mitragyninMin - {{ $min }}) / ({{ $max }} - {{ $min }})))}; --max-ratio: ${Math.max(0, Math.min(1, ($store.catalog.mitragyninMax - {{ $min }}) / ({{ $max }} - {{ $min }})))};`"
                            >
                                <div class="range-dual__fill"></div>
                                <input
                                    type="range"
                                    class="range-dual__input range-dual__input--min"
                                    min="{{ $min }}"
                                    max="{{ $max }}"
                                    step="{{ $step }}"
                                    :value="$store.catalog.mitragyninMin"
                                    @input="$store.catalog.onMitragyninMin($event)"
                                    aria-label="Minimum {{ $title }}"
                                >
                                <input
                                    type="range"
                                    class="range-dual__input range-dual__input--max"
                                    min="{{ $min }}"
                                    max="{{ $max }}"
                                    step="{{ $step }}"
                                    :value="$store.catalog.mitragyninMax"
                                    @input="$store.catalog.onMitragyninMax($event)"
                                    aria-label="Maximum {{ $title }}"
                                >
                            </div>
                            <div class="range-dual__labels">
                                <span><span x-text="$store.catalog.mitragyninMin.toFixed(2).replace('.', ',')"></span>{{ $unit }}</span>
                                <span><span x-text="$store.catalog.mitragyninMax.toFixed(2).replace('.', ',')"></span>{{ $unit }}</span>
                            </div>
                        </div>

                    @elseif($key === 'color')
                        <div class="color-tags">
                            @foreach($group['options'] ?? [] as $opt)
                                @php
                                    $value    = $opt['value'] ?? null;
                                    $disabled = $opt['disabled'] ?? false;
                                    $img      = $colorImages[$value] ?? null;
                                @endphp
                                <button
                                    type="button"
                                    class="color-tag"
                                    :class="$store.catalog.isOn('colors', @js($value)) && 'color-tag--active'"
                                    @if($disabled) disabled @endif
                                    @click="$store.catalog.toggle('colors', @js($value))"
                                    :aria-pressed="$store.catalog.isOn('colors', @js($value))"
                                >
                                    @if($img)
                                        <span class="color-tag__media">
                                            <img src="{{ $img }}" alt="" loading="lazy" />
                                        </span>
                                    @else
                                        <span class="color-tag__media vein-dot vein-dot--{{ $opt['vein'] ?? 'green' }}"></span>
                                    @endif
                                    <span class="color-tag__label">{{ mb_strtoupper($opt['label']) }}</span>
                                    <span class="color-tag__count" x-text="$store.catalog.countFor('colors', @js($value))"></span>
                                </button>
                            @endforeach
                        </div>

                    @else
                        <div class="tag-group">
                            @foreach($group['options'] ?? [] as $opt)
                                @php
                                    $value    = $opt['value'] ?? null;
                                    $disabled = $opt['disabled'] ?? false;
                                    $bindable = $stateKey && $value && !$disabled;
                                    $dotClass = $key === 'availability' ? ($availabilityDots[$opt['label']] ?? null) : null;
                                @endphp

                                @if($bindable)
                                    <button
                                        type="button"
                                        class="tag"
                                        :class="[$store.catalog.isOn(@js($stateKey), @js($value)) && 'tag--active', $store.catalog.countFor(@js($stateKey), @js($value)) === 0 && 'tag--empty']"
                                        @click="$store.catalog.toggle(@js($stateKey), @js($value))"
                                        :aria-pressed="$store.catalog.isOn(@js($stateKey), @js($value))"
                                    >
                                        @if($dotClass)
                                            <span class="tag__dot {{ $dotClass }}"></span>
                                        @endif
                                        <span class="tag__label">{{ $opt['label'] }}</span>
                                        <span class="tag__count" x-text="$store.catalog.countFor(@js($stateKey), @js($value))"></span>
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        class="tag tag--disabled"
                                        @disabled($disabled)
                                    >
                                        @if($dotClass)
                                            <span class="tag__dot {{ $dotClass }}"></span>
                                        @endif
                                        <span class="tag__label">{{ $opt['label'] }}</span>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach
        </div>

        <footer class="filter-modal__foot">
            <button type="button" class="filter-modal__reset" @click="$store.catalog.clear()">
                Vyčistit
            </button>
            <x-ui.button variant="primary" size="md" x-on:click="closeModal()">
                Zobrazit <span x-text="$store.catalog.visibleCount"></span> produktů
            </x-ui.button>
        </footer>
    </aside>
</div>
