@props([
    'products' => [],
    'total' => null,
    'activeFilters' => [],   // [['label' => 'Zelený', 'value' => 'zeleny'], …]
    'fullCatalogHref' => null, // when set, the empty-state CTA links here instead of clearing filters
    'fullCatalogCount' => null, // count to display in the empty-state CTA
])

@php($total = $total ?? count($products))
@php($emptyCount = $fullCatalogCount ?? $total)

<section class="product-grid-area" aria-labelledby="grid-title">
    <h2 id="grid-title" class="sr-only">Produkty</h2>

    <div class="product-grid-area__bar">
        <p class="product-grid-area__count">
            Zobrazujeme <strong><span x-text="$store.catalog.visibleCount">{{ count($products) }}</span> z {{ $total }} produktů</strong>
        </p>

        <label class="sort-dropdown">
            <span class="sr-only">Řazení</span>
            <select class="sort-dropdown__select">
                <option>Doporučené</option>
                <option>Cena: nejlevnější</option>
                <option>Cena: nejdražší</option>
                <option>Nejnovější</option>
                <option>Mitragynin %</option>
                <option>Hodnocení</option>
            </select>
            <span class="sort-dropdown__icon"><x-ui.icon name="chevron-down" :size="16" /></span>
        </label>
    </div>

    @if(!empty($activeFilters))
        <div class="active-filters">
            @foreach($activeFilters as $f)
                <span class="chip chip--active-filter">
                    {{ $f['label'] }}
                    <button type="button" aria-label="Odstranit filtr"><x-ui.icon name="x" :size="12" /></button>
                </span>
            @endforeach
            <button type="button" class="active-filters__clear">Vyčistit vše</button>
        </div>
    @endif

    <div class="catalog-empty" x-show="$store.catalog.visibleCount === 0" x-cloak>
        <x-ui.icon name="search" :size="32" />
        <h3 class="catalog-empty__title">Žádné produkty neodpovídají vašemu výběru</h3>
        <p class="catalog-empty__lead">Zkuste uvolnit filtry nebo se podívejte na celý sortiment.</p>
        @if($fullCatalogHref)
            <x-ui.button :href="$fullCatalogHref" variant="primary" size="md" icon="arrow-right">
                Přejít do plného katalogu ({{ $emptyCount }} produktů)
            </x-ui.button>
        @else
            <x-ui.button variant="primary" size="md" x-on:click="$store.catalog.clear()">
                Zobrazit všechny produkty (<span x-text="$store.catalog.totalCount"></span>)
            </x-ui.button>
        @endif
    </div>

    <div class="product-grid" id="grid" x-show="$store.catalog.visibleCount > 0">
        @foreach($products as $p)
            <div
                data-product-card
                data-color="{{ $p['color'] ?? '' }}"
                data-strain="{{ $p['strain'] ?? '' }}"
                data-form="{{ $p['form'] ?? '' }}"
                data-mitragynin="{{ $p['mitragynin'] ?? '' }}"
                x-show="$store.catalog.matches({ color: @js($p['color'] ?? ''), strain: @js($p['strain'] ?? ''), form: @js($p['form'] ?? ''), mitragynin: @js($p['mitragynin'] ?? '') })"
            >
                <x-ui.product-card
                    :name="$p['name']"
                    :slug="$p['slug']"
                    :strainLabel="$p['colorLabel'] . ' · ' . $p['strainLabel']"
                    :vein="$p['vein'] ?? 'green'"
                    :mitragynin="$p['mitragynin'] . ' % · ' . $p['grind']"
                    :price25="$p['price25']"
                    :price50="$p['price50']"
                    :rating="$p['rating']"
                    :reviewsCount="$p['reviewsCount'] . ' recenzí'"
                    :badge="$p['badge']"
                    :href="\App\Support\Locale::url('/kratom/' . $p['slug'])"
                    :image="asset(ltrim($p['image'], '/'))"
                />
            </div>
        @endforeach
    </div>

    @if($total > count($products))
        <div class="product-grid-area__more">
            <x-ui.button variant="outline-light" size="lg">
                Načíst dalších 12 produktů
            </x-ui.button>
        </div>

        <nav class="pagination" aria-label="Stránky">
            <a href="#" class="pagination__btn" aria-label="Předchozí"><x-ui.icon name="arrow-left" :size="16" /></a>
            <a href="#" class="pagination__link pagination__link--active" aria-current="page">1</a>
            <a href="#" class="pagination__link">2</a>
            <a href="#" class="pagination__link">3</a>
            <a href="#" class="pagination__btn" aria-label="Další"><x-ui.icon name="arrow-right" :size="16" /></a>
        </nav>
    @endif
</section>
