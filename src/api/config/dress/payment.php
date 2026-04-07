<?php

return [
    'methods' => [
        [
            'name' => 'default',
            'type' => 'cash',
            'label' => 'Оплата при получении',
        ],
        [
            'name' => 'zasilkovna',
            'type' => 'cod',
            'label' => 'Наложенный платеж Zasilkovna',
        ],
        [
            'name' => 'novaposhta',
            'type' => 'cod',
            'label' => 'Наложенный платеж Новая Почта',
        ],
        [
            'name' => 'messenger',
            'type' => 'cod',
            'label' => 'Наложенный платеж Messenger.cz',
        ],
        [
            'name' => 'liqpay',
            'type' => 'online',
            'label' => 'Оплата LiqPay',
        ],
        [
            'name' => 'niftipay',
            'type' => 'online',
            'label' => 'Оплата Niftipay',
        ],
        [
            'name' => 'card',
            'type' => 'online',
            'label' => 'Оплата картой',
        ],
        [
            'name' => 'bank',
            'type' => 'transfer',
            'label' => 'Банковский перевод',
        ],
    ],
];
