@props([
    'groups' => [],
    'products' => [],
    'total' => null,
    'activeFilters' => [],
])

<section class="section section--cream-50 catalog-main">
    <div class="container catalog-main__inner">
        <div class="catalog-main__sidebar">
            <x-catalog.filter-sidebar :groups="$groups" />
        </div>

        <div class="catalog-main__grid">
            <div class="catalog-main__mobile-filters">
                <x-ui.button variant="outline-light" size="md" icon="menu" iconPosition="left">
                    Filtry ({{ count($activeFilters) }})
                </x-ui.button>
            </div>

            <x-catalog.product-grid :products="$products" :total="$total" :activeFilters="$activeFilters" />
        </div>
    </div>
</section>
