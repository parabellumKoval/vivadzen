<?php

namespace App\Support\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AttributeValueFetchQuery
{
    public function __construct(
        protected Request $request,
    ) {
    }

    public function __invoke(Builder $query): Builder
    {
        $attributeId = $this->request->get('attribute_id');

        if ($attributeId) {
            $query->where('attribute_id', $attributeId);
        }

        return $query;
    }
}
