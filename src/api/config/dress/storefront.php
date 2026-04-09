<?php

return [
    'enabled' => true,

    'default' => 'main',

    'apply_unassigned_to_default' => true,

    'values' => [
        'main' => [
            'label' => 'Основной storefront',
            'badge' => [
                'background' => '#E5E7EB',
                'color' => '#111827',
            ],
        ],
        'kratom' => [
            'label' => 'Kratom storefront',
            'badge' => [
                'background' => '#DCFCE7',
                'color' => '#166534',
            ],
        ],
        'telegram' => [
            'label' => 'Telegram Mini App storefront',
            'badge' => [
                'background' => '#DBEAFE',
                'color' => '#1D4ED8',
            ],
        ],
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
