<?php

return [
    'fetch_default_paginate' => 50,

    'fetchables' => [
        'user' => [
            'model' => App\Models\User::class,
            'with' => ['profile'],
            'columns' => ['name', 'email'],
            'relation_columns' => [
                'profile' => ['phone', 'country_code'],
            ],
            'search_id' => true,
            'id_prefixes' => ['#'],
        ],
        'brand' => [
            'model' => Backpack\Store\app\Models\Brand::class,
            'columns' => ['name', 'slug'],
            'search_id' => true,
            'id_prefixes' => ['#'],
        ],
        'category' => [
            'model' => Backpack\Store\app\Models\Category::class,
            'columns' => ['name', 'slug'],
            'search_id' => true,
            'id_prefixes' => ['#'],
        ],
        'product' => [
            'model' => Backpack\Store\app\Models\Product::class,
            'columns' => ['name', 'code', 'slug'],
            'relation_columns' => [
                'brand' => ['name', 'slug'],
                'categories' => ['name', 'slug'],
            ],
            'search_id' => true,
            'id_prefixes' => ['#'],
        ],
        'tag' => [
            'model' => Backpack\Tag\app\Models\Tag::class,
            'columns' => ['text', 'slug'],
            'search_id' => true,
            'id_prefixes' => ['#'],
        ],
        'attribute' => [
            'model' => Backpack\Store\app\Models\Attribute::class,
            'columns' => ['name', 'slug'],
            'search_id' => true,
            'id_prefixes' => ['#'],
        ],
        'attribute_value' => [
            'model' => Backpack\Store\app\Models\AttributeValue::class,
            'columns' => ['value'],
            'relation_columns' => [
                'attribute' => ['name', 'slug'],
            ],
            'search_id' => true,
            'id_prefixes' => ['#'],
            'query' => function ($query) {
                $attributeId = request()->get('attribute_id');

                if ($attributeId) {
                    $query->where('attribute_id', $attributeId);
                }

                return $query;
            },
        ],
        'merchant_category' => [
            'model' => Backpack\Store\app\Models\MerchantCategory::class,
            'columns' => ['key', 'name'],
            'search_id' => true,
            'id_prefixes' => ['#'],
        ],
        'review' => [
            'model' => Backpack\Reviews\app\Models\Review::class,
            'columns' => ['text'],
            'relation_columns' => [
                'user' => ['name', 'email'],
            ],
            'search_id' => true,
            'id_prefixes' => ['#'],
        ],
    ],
];
