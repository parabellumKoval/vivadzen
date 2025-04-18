<?php

return [
    'entity_name' => [
        'singular' => 'AI Generation History',
        'plural' => 'AI Generation History',
    ],
    'filters' => [
        'type' => 'Type',
        'status' => 'Status',
    ],
    'columns' => [
        'generatable_type' => 'Type',
        'generatable' => 'Item',
        'status' => 'Status',
        'message' => 'Message',
        'updated_at' => 'Last update',
    ],
    'settings' => [
        'success' => 'AI generation settings have been saved successfully.',
        'error' => 'Error saving AI generation settings.',
    ],
    'widget' => [
        'title' => 'AI Generation Settings',
        'general' => [
            'title' => 'General Settings',
            'auto_generation_enabled' => 'Enable automatic AI generation',
        ],
        'product' => [
            'title' => 'Product Settings',
            'generate_for_products' => 'Generate for products',
            'active_products_only' => 'Only for active products',
            'in_stock_products_only' => 'Only for in-stock products',
            'min_price' => 'Minimum product price',
        ],
        'category' => [
            'title' => 'Category Settings',
            'generate_for_categories' => 'Generate for categories',
            'active_categories_only' => 'Only for active categories',
            'categories_with_products_only' => 'Only for categories with products',
        ],
        'save' => 'Save Settings',
    ],
];