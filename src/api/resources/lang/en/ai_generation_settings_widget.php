<?php

return [
    'settings_title' => 'AI Generation Settings',
    'settings_description' => 'Configure AI automatic generation parameters for products and categories',
    
    // General settings
    'auto_generation_enabled' => 'Enable AI automatic generation',
    'auto_generation_enabled_hint' => 'When enabled, the system will automatically generate AI content according to the settings below',
    
    // Product settings
    'products_title' => 'Products',
    'generate_product_description' => 'Generate product description',
    'generate_description_hint' => 'Enable AI automatic generation for products',
    'active_products_only' => 'Only for active products',
    'active_products_only_hint' => 'Generate AI content only for products that are currently active in the catalog',
    'in_stock_products_only' => 'Only for in-stock products',
    'in_stock_products_only_hint' => 'Generate AI content only for products that are currently in stock',
    'min_price' => 'Minimum product price',
    'min_price_hint' => 'Generate AI content only for products with price above the specified value',
    'detect_brand' => 'Detect product brand',
    'detect_brand_hint' => 'AI will try to determine the product brand based on its name and description',
    'detect_category' => 'Detect product category',
    'detect_category_hint' => 'AI will try to determine the appropriate category for the product',
    'fill_characteristics' => 'Fill characteristics',
    'fill_characteristics_hint' => 'AI will analyze the product and fill in its characteristics',
    
    // Category settings
    'categories_title' => 'Categories',
    'generate_for_categories' => 'Generate for categories',
    'generate_for_categories_hint' => 'Enable AI automatic generation for categories',
    'active_categories_only' => 'Only for active categories',
    'active_categories_only_hint' => 'Generate AI content only for categories that are currently active',
    'categories_with_products_only' => 'Only for categories with products',
    'categories_with_products_only_hint' => 'Generate AI content only for categories containing products',
    
    // Common
    'save_changes' => 'Save settings',
];