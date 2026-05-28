@props([
    'products' => [],
])

<section class="section section--paper related-section" aria-labelledby="related-title">
    <div class="container">
        <h2 id="related-title" class="t-heading-xl t-on-light-accent">Mohlo by vás zaujmout</h2>

        <div class="related-grid mt-8">
            @foreach($products as $p)
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
            @endforeach
        </div>
    </div>
</section>
