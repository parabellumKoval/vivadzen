<?php

namespace App\Observers;

use App\Models\Taxonomy;
use App\Services\CacheWarmer;

class TaxonomyObserver
{
    public function __construct(private readonly CacheWarmer $warmer)
    {
    }

    public function saved(Taxonomy $tax): void
    {
        $this->warmer->warmTaxonomy($tax);
    }

    public function deleted(Taxonomy $tax): void
    {
        // Полный rebuild индекса этого типа — проще и безопаснее точечного evict.
        $this->warmer->warmTaxonomies();
    }
}
