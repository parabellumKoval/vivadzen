<?php

return [
    'entity_name' => [
        'singular' => 'История AI генерации',
        'plural' => 'История AI генерации',
    ],

    'filters' => [
        'type' => 'Тип',
        'status' => 'Статус',
    ],

    'columns' => [
        'generatable_type' => 'Тип',
        'generatable' => 'Элемент',
        'status' => 'Статус',
        'message' => 'Сообщение',
        'updated_at' => 'Последнее обновление',
    ],

    'settings' => [
        'success' => 'Настройки AI генерации успешно сохранены.',
        'error' => 'Ошибка при сохранении настроек AI генерации.',
    ],

    'widget' => [
        'title' => 'Настройки AI генерации',
        'general' => [
            'title' => 'Общие настройки',
            'auto_generation_enabled' => 'Включить автоматическую генерацию AI',
        ],
        'product' => [
            'title' => 'Настройки товаров',
            'generate_for_products' => 'Генерировать для товаров',
            'active_products_only' => 'Только для активных товаров',
            'in_stock_products_only' => 'Только для товаров в наличии',
            'min_price' => 'Минимальная цена товара',
        ],
        'category' => [
            'title' => 'Настройки категорий',
            'generate_for_categories' => 'Генерировать для категорий',
            'active_categories_only' => 'Только для активных категорий',
            'categories_with_products_only' => 'Только для категорий с товарами',
        ],
        'save' => 'Сохранить настройки',
    ],
];