<?php

return [
    'settings_title' => 'Image Generation Settings',
    'settings_description' => 'Configure automatic image generation settings for products and categories',
    
    // General settings
    'auto_generation_enabled' => 'Enable automatic image generation',
    'auto_generation_enabled_hint' => 'When enabled, the system will automatically generate images according to the settings below',
    'use_ai_image_suggestion' => 'Use AI to suggest image concepts',
    'use_ai_image_suggestion_hint' => 'AI will analyze product/category data to suggest better image concepts',
    
    // Product settings
    'products_title' => 'Products',
    'generate_for_products' => 'Generate images for products',
    'generate_for_products_hint' => 'Enable automatic image generation for products',
    'active_products_only' => 'Only for active products',
    'active_products_only_hint' => 'Generate images only for products that are currently active in the catalog',
    'in_stock_products_only' => 'Only for in-stock products',
    'in_stock_products_only_hint' => 'Generate images only for products that are currently in stock',
    'min_price' => 'Minimum product price',
    'min_price_hint' => 'Generate images only for products with price higher than this value',
    'product_images_count' => 'Number of images per product',
    'product_images_count_hint' => 'How many images to generate for each product',
    
    // Category settings
    'categories_title' => 'Categories',
    'generate_for_categories' => 'Generate images for categories',
    'generate_for_categories_hint' => 'Enable automatic image generation for categories',
    'active_categories_only' => 'Only for active categories',
    'active_categories_only_hint' => 'Generate images only for categories that are currently active',
    'categories_with_products_only' => 'Only for categories with products',
    'categories_with_products_only_hint' => 'Generate images only for categories that contain products',
    'category_images_count' => 'Number of images per category',
    'category_images_count_hint' => 'How many images to generate for each category',
    
    // Common
    'save_changes' => 'Save Changes',
];