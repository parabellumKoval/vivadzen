<?php

namespace App\Support\Helpers;

use Illuminate\Database\Eloquent\Builder;

class ProductBaseFetchQuery
{
    public function __invoke(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->whereNull('parent_id')
                ->orWhere('parent_id', 0);
        });
    }
}
