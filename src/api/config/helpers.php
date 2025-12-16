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
        'catalog' => [
            'model' => Backpack\Store\app\Models\Catalog::class,
            'columns' => ['name', 'short_name', 'code', 'slug'],
            'relation_columns' => [],
            'search_id' => true,
            'id_prefixes' => ['#'],
        ],
        'catalog_reviewable' => [
            'model' => Backpack\Store\app\Models\Catalog::class,
            'columns' => ['name', 'short_name', 'code', 'slug'],
            'relation_columns' => [],
            'search_id' => true,
            'id_prefixes' => ['#'],
            'key_column' => 'group_id',
        ],
        'product' => [
            'model' => App\Models\Product::class,
            'columns' => ['name', 'code', 'slug'],
            'relation_columns' => [
                'brand' => ['name', 'slug'],
                'categories' => ['name', 'slug'],
            ],
            'search_id' => true,
            'id_prefixes' => ['#'],
        ],
        'product_base' => [
            'model' => App\Models\Product::class,
            'columns' => ['name', 'code', 'slug'],
            'relation_columns' => [
                'brand' => ['name', 'slug'],
                'categories' => ['name', 'slug'],
            ],
            'search_id' => true,
            'id_prefixes' => ['#'],
            'query' => App\Support\Helpers\ProductBaseFetchQuery::class,
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
            'query' => App\Support\Helpers\AttributeValueFetchQuery::class,
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
