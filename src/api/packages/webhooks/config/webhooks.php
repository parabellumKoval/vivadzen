<?php

return [
    'frontend_url' => env('FRONT_URL', 'http://host.docker.internal:3000'),
    'timeout' => 30,
    'queue' => env('WEBHOOK_QUEUE'),
    'retry' => [
        'times' => 2,
        'sleep' => 1000,
    ],

    'widget' => [
        'title' => 'Frontend Webhooks',
        'description' => 'Ручной прогрев, события и CRON обновления фронтенда',
        'grid_columns' => 3,
        'show_last_refresh' => true,
        'show_status' => true,
    ],

    'units' => [
        'refresh_settings' => [
            'title' => 'Обновить настройки',
            'desc' => 'Обновить все настройки на сайте',
            'url' => '/api/_refresh-settings',
            'button' => 'Обновить настройки',
            'icon' => 'la-cog',
            'color' => 'btn-primary',
            'events' => ['settings.updated'],
        ],
        'refresh_currency' => [
            'title' => 'Обновить курсы валют',
            'desc' => 'Обновить курс валют на frontend',
            'url' => '/api/_converter/refresh',
            'button' => 'Обновить курсы валют',
            'icon' => 'la-coins',
            'color' => 'btn-primary',
            'events' => ['currency_rates.updated'],
            'schedules' => ['currency-hourly'],
        ],
        'refresh_categories' => [
            'title' => 'Обновить категории',
            'desc' => 'Обновить категории на сайте',
            'url' => [
                '/api/_categories/refresh/slugs',
                '/api/_categories/refresh/list',
                '/api/_categories/refresh/main',
            ],
            'button' => 'Обновить категории',
            'icon' => 'la-list-alt',
            'color' => 'btn-info',
            'events' => ['categories.changed'],
        ],
        'refresh_category_slug' => [
            'title' => 'Обновить категорию по slug',
            'desc' => 'Фоновое обновление конкретной категории (только события)',
            'url' => '/api/_categories/refresh/:slug',
            'button' => 'Обновить категорию',
            'icon' => 'la-tag',
            'color' => 'btn-outline-secondary',
            'visible_in_widget' => false,
            'events' => ['categories.changed'],
            'payload' => [
                'placeholder_key' => 'slug',
                'batch' => [
                    'url' => '/api/_categories/refresh/slugs',
                    'body_key' => 'slugs',
                ],
            ],
            'event_buffer' => [
                'delay' => 5,
                'max_items' => 40,
                'ttl' => 120,
            ],
        ],
        'refresh_homepage_lists' => [
            'title' => 'Списки на главной',
            'desc' => 'Обновить списки товаров на главной странице',
            'url' => '/api/_fetcher/homepage-main-lists/refresh',
            'button' => 'Обновить списки',
            'icon' => 'la-shopping-cart',
            'color' => 'btn-success',
            'events' => ['homepage.lists.updated'],
            'schedules' => ['homepage-lists-half-hour'],
        ],
        'refresh_homepage_articles' => [
            'title' => 'Статьи на главной',
            'desc' => 'Обновить статьи на главной странице',
            'url' => '/api/_fetcher/homepage-main-articles/refresh',
            'button' => 'Обновить статьи',
            'icon' => 'la-newspaper',
            'color' => 'btn-warning',
            'events' => ['homepage.articles.updated'],
            'schedules' => ['homepage-articles-hourly'],
        ],
        'refresh_video_reviews' => [
            'title' => 'Видео отзывы',
            'desc' => 'Обновить видео отзывы на главной странице',
            'url' => '/api/_fetcher/homepage-video-reviews/refresh',
            'button' => 'Обновить отзывы',
            'icon' => 'la-video',
            'color' => 'btn-danger',
            'events' => ['homepage.video_reviews.updated'],
            'schedules' => ['homepage-videos-hourly'],
        ],
    ],

    'events' => [
        'settings.updated' => [
            'description' => 'Любые изменения настроек',
            'sources' => [
                [
                    'class' => Backpack\Settings\Events\SettingsGroupChanged::class,
                    'resolver' => ParabellumKoval\Webhooks\Resolvers\SettingsGroupChangedResolver::class,
                ],
            ],
        ],
        'currency_rates.updated' => [
            'description' => 'Обновление курсов валют',
            'sources' => [
                [
                    'class' => Backpack\Store\app\Events\CurrencyRateChanged::class,
                    'resolver' => ParabellumKoval\Webhooks\Resolvers\CurrencyRateChangedResolver::class,
                ],
            ],
        ],
        'categories.changed' => [
            'description' => 'Создание/редактирование/удаление категорий',
            'sources' => [
                [
                    'class' => Backpack\Store\app\Events\CategoryChanged::class,
                    'resolver' => ParabellumKoval\Webhooks\Resolvers\CategoryChangedResolver::class,
                ],
            ],
        ],
        'homepage.lists.updated' => [
            'description' => 'Обновление списков на главной',
            'sources' => [
                [
                    'class' => Backpack\Store\app\Events\ProductListChanged::class,
                    'resolver' => ParabellumKoval\Webhooks\Resolvers\ProductListChangedResolver::class,
                ],
            ],
        ],
        'homepage.articles.updated' => [
            'description' => 'Обновление статей',
            'sources' => [
                [
                    'class' => Backpack\Articles\app\Events\ArticleChanged::class,
                    'resolver' => ParabellumKoval\Webhooks\Resolvers\ArticleChangedResolver::class,
                ],
            ],
        ],
        'homepage.video_reviews.updated' => [
            'description' => 'Видео отзывы / отзывы',
            'sources' => [
                [
                    'class' => Backpack\Reviews\app\Events\ReviewChanged::class,
                    'resolver' => ParabellumKoval\Webhooks\Resolvers\ReviewChangedResolver::class,
                ],
            ],
        ],
    ],

    'schedules' => [
        'currency-hourly' => [
            'cron' => '15 * * * *',
            'units' => ['refresh_currency'],
            'description' => 'Обновить курсы каждый час после синхронизации',
        ],
        'homepage-lists-half-hour' => [
            'cron' => '*/30 * * * *',
            'units' => ['refresh_homepage_lists'],
            'description' => 'Переобновлять списки на главной каждые 30 минут',
        ],
        'homepage-articles-hourly' => [
            'cron' => '5 * * * *',
            'units' => ['refresh_homepage_articles'],
            'description' => 'Обновлять блок статей каждый час',
        ],
        'homepage-videos-hourly' => [
            'cron' => '10 * * * *',
            'units' => ['refresh_video_reviews'],
            'description' => 'Обновлять видеоотзывы каждый час',
        ],
    ],

    'defaults' => [
        'event_buffer' => [
            'delay' => 3,
            'max_items' => 25,
            'ttl' => 120,
        ],
    ],
];
