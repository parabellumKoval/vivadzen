<?php

return [
    'api_url' => env('NOVAPOSHTA_API_URL', 'https://api.novaposhta.ua/v2.0/json/'),
    'api_key' => env('NOVAPOSHTA_KEY'),

    'sync' => [
        'settlements_limit' => env('NOVAPOSHTA_SETTLEMENTS_LIMIT', 150),
        'warehouses_limit' => env('NOVAPOSHTA_WAREHOUSES_LIMIT', 100),
    ],

    'indexes' => [
        'settlements' => env('NOVAPOSHTA_MEILI_INDEX_SETTLEMENTS', 'np_settlements'),
        'warehouses' => env('NOVAPOSHTA_MEILI_INDEX_WAREHOUSES', 'np_warehouses'),
    ],

    'popular_settlements' => [
        ['uk' => 'Київ', 'ru' => 'Киев'],
        ['uk' => 'Харків', 'ru' => 'Харьков'],
        ['uk' => 'Одеса', 'ru' => 'Одесса'],
        ['uk' => 'Львів', 'ru' => 'Львов'],
        ['uk' => 'Дніпро', 'ru' => 'Днепр'],
        ['uk' => 'Запоріжжя', 'ru' => 'Запорожье'],
    ],
];
