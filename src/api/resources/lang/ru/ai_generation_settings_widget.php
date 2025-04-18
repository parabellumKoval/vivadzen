<?php

return [
    'settings_title' => 'Настройки AI генерации',
    'settings_description' => 'Настройте параметры автоматической генерации AI для товаров и категорий',
    
    // General settings
    'auto_generation_enabled' => 'Включить автоматическую генерацию AI',
    'auto_generation_enabled_hint' => 'Когда включено, система будет автоматически генерировать AI контент в соответствии с настройками ниже',
    
    // Product settings
    'products_title' => 'Товары',
    'generate_product_description' => 'Генерировать описание товара',
    'generate_description_hint' => 'Включить автоматическую генерацию описания для товаров',
    'active_products_only' => 'Только для активных товаров',
    'active_products_only_hint' => 'Генерировать AI контент только для товаров, которые сейчас активны в каталоге',
    'in_stock_products_only' => 'Только для товаров в наличии',
    'in_stock_products_only_hint' => 'Генерировать AI контент только для товаров, которые сейчас в наличии',
    'min_price' => 'Минимальная цена товара',
    'min_price_hint' => 'Генерировать AI контент только для товаров с ценой выше указанного значения',
    'detect_brand' => 'Определять бренд товара',
    'detect_brand_hint' => 'ИИ будет пытаться определить бренд товара на основе его названия и описания',
    'detect_category' => 'Определять категорию товара',
    'detect_category_hint' => 'ИИ будет пытаться определить подходящую категорию для товара',
    'fill_characteristics' => 'Заполнять характеристики',
    'fill_characteristics_hint' => 'ИИ будет анализировать товар и заполнять его характеристики',
    
    // Category settings
    'categories_title' => 'Категории',
    'generate_for_categories' => 'Генерировать для категорий',
    'generate_for_categories_hint' => 'Включить автоматическую генерацию AI для категорий',
    'active_categories_only' => 'Только для активных категорий',
    'active_categories_only_hint' => 'Генерировать AI контент только для категорий, которые сейчас активны',
    'categories_with_products_only' => 'Только для категорий с товарами',
    'categories_with_products_only_hint' => 'Генерировать AI контент только для категорий, содержащих товары',
    
    // Common
    'save_changes' => 'Сохранить настройки',
];