@props([
    'groups' => [],
    'products' => [],
    'total' => null,
    'activeFilters' => [],
    'fullCatalogHref' => null,
    'fullCatalogCount' => null,
])

{{-- V2 catalog layout: no sidebar, full-width 3-col grid, filters in modal.
     Trigger button lives on the chip-row (pass :withFilter to <x-catalog.chip-row>). --}}
<section class="section section--cream-50 catalog-main catalog-main--v2">
    <div class="container">
        <div class="catalog-main__grid">
            <x-catalog.product-grid
                :products="$products"
                :total="$total"
                :activeFilters="$activeFilters"
                :fullCatalogHref="$fullCatalogHref"
                :fullCatalogCount="$fullCatalogCount"
            />
        </div>
    </div>

    @push('modals')
        {{-- Render the filter modal at body level via the layout's
             @stack('modals'), so it escapes <main>'s isolate stacking
             context and can sit above the sticky header. --}}
        <x-catalog.filter-modal :groups="$groups" :total="$total" />
    @endpush
</section>
