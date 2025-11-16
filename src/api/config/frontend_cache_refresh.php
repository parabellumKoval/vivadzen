<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Frontend Cache Refresh Units Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the cache refresh units that will be displayed
    | in the admin dashboard as widgets with buttons to trigger cache refresh
    | operations on the frontend.
    |
    */

    'units' => [
        [
            'title' => 'Обновить настройки',
            'desc' => 'Обновить все настройки на сайте',
            'url' => '/api/_refresh-settings',
            'button' => 'Обновить настройки',
            'icon' => 'la-cog',
            'color' => 'btn-primary',
        ],
        [
            'title' => 'Обновить курсы валют',
            'desc' => 'Обновить курс валют на frontend',
            'url' => '/api/_converter/refresh',
            'button' => 'Обновить курсы валют',
            'icon' => 'la-cog',
            'color' => 'btn-primary',
        ],
        [
            'title' => 'Обновить категории',
            'desc' => 'Обновить категории на сайте',
            'url' => [
              '/api/_categories/refresh/slugs',
              '/api/_categories/refresh/list',
            ],
            'button' => 'Обновить категории',
            'icon' => 'la-list-alt',
            'color' => 'btn-info',
        ],
        [
            'title' => 'Списки на главной',
            'desc' => 'Обновить списки товаров на главной странице',
            'url' => '/api/_fetcher/homepage-main-lists/refresh',
            'button' => 'Обновить списки',
            'icon' => 'la-shopping-cart',
            'color' => 'btn-success',
        ],
        [
            'title' => 'Статьи на главной',
            'desc' => 'Обновить статьи на главной странице',
            'url' => '/api/_fetcher/homepage-main-articles/refresh',
            'button' => 'Обновить статьи',
            'icon' => 'la-users',
            'color' => 'btn-warning',
        ],
        [
            'title' => 'Обновить видео отзывы',
            'desc' => 'Обновить видео отзывы на главной странице',
            'url' => '/api/_fetcher/homepage-video-reviews/refresh',
            'button' => 'Обновить отзывы',
            'icon' => 'la-trash',
            'color' => 'btn-danger',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your frontend application. This should match the
    | FRONT_URL environment variable. Use host.docker.internal if running
    | in Docker and frontend is on the host machine.
    |
    */
    'frontend_url' => env('FRONT_URL', 'http://host.docker.internal:3000/'),
    // 'frontend_url' => "http://localhost:3000/",

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout (in seconds) for HTTP requests to the frontend.
    |
    */
    'timeout' => 30,

    /*
    |--------------------------------------------------------------------------
    | Widget Display Options
    |--------------------------------------------------------------------------
    |
    */
    'widget' => [
        'title' => 'Frontend Cache Management',
        'description' => 'Manage and refresh frontend cache from admin panel',
        'grid_columns' => 3, // Number of buttons per row
        'show_last_refresh' => true,
        'show_status' => true,
    ],
];