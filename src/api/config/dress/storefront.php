<?php

return [
    'enabled' => true,

    'default' => 'main',

    'apply_unassigned_to_default' => true,

    'values' => [
        'main' => [
            'label' => 'Основной storefront',
            'frontend_url' => rtrim((string) env('CLIENT_URL_MAIN', env('NIFTIPAY_CLIENT_URL_MAIN', env('CLIENT_URL', env('FRONT_URL', 'http://host.docker.internal:3000')))), '/'),
            'badge' => [
                'background' => '#E5E7EB',
                'color' => '#111827',
            ],
        ],
        'kratom' => [
            'label' => 'Kratom storefront',
            'frontend_url' => rtrim((string) env('CLIENT_URL_KRATOM', env('NIFTIPAY_CLIENT_URL_KRATOM', 'http://host.docker.internal:3001')), '/'),
            'badge' => [
                'background' => '#DCFCE7',
                'color' => '#166534',
            ],
        ],
        'telegram' => [
            'label' => 'Telegram Mini App storefront',
            'frontend_url' => rtrim((string) env('CLIENT_URL_TELEGRAM', env('FRONT_URL_TELEGRAM', env('CLIENT_URL', env('FRONT_URL', 'http://host.docker.internal:3000')))), '/'),
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
