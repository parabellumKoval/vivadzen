<?php

namespace Backpack\CRUD\app\Library\ServiceOperation\Similar\Contracts;

use Backpack\CRUD\app\Library\ServiceOperation\Similar\SimilarSearchContext;
use Illuminate\Support\Collection;

interface SimilarSearchProvider
{
    /**
     * Execute the provider search and return the ordered collection of results.
     *
     * Each result item should be an array that contains:
     *  - model: the Eloquent model instance representing the candidate
     *  - score: optional numeric similarity score (0-100 scale is recommended)
     *  - meta: provider-specific metadata (country, source, etc.)
     *
     * @return \Illuminate\Support\Collection<int, array{model: \Illuminate\Database\Eloquent\Model, score?: float|null, meta?: array}>
     */
    public function search(SimilarSearchContext $context, array $params = []): Collection;
}
