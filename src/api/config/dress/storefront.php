<?php

return [
    'enabled' => true,

    'default' => 'main',

    'apply_unassigned_to_default' => true,

    'values' => [
        'main' => 'Основной storefront',
        'kratom' => 'Kratom storefront',
        'telegram' => 'Telegram Mini App storefront',
    ],

    'settings_overrides' => [
        'kratom' => [
            'shipping.add_to_order_enabled' => 'shipping.kratom.add_to_order_enabled',
            'shipping.free_enabled' => 'shipping.kratom.free_enabled',
            'shipping.free_min_price' => 'shipping.kratom.free_min_price',
            'shipping.methods' => 'shipping.kratom.methods',
            'payment.methods' => 'payment.kratom.methods',
        ],
    ],
];
