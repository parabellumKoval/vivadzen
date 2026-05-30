<?php

/*
|--------------------------------------------------------------------------
| Media storage configuration
|--------------------------------------------------------------------------
| Управляет тем, куда заливаются картинки (галерея товара + редактор):
| local — на диск `public` (storage/app/public, отдаётся через /storage)
| bunny — в BunnyCDN Storage, отдача через CDN-pull-zone
*/

return [
    'driver' => env('MEDIA_DRIVER', 'local'),

    'local' => [
        'disk' => env('MEDIA_LOCAL_DISK', 'public'),
        // публичный префикс относительно домена приложения
        'url_prefix' => env('MEDIA_LOCAL_URL_PREFIX', '/storage'),
    ],

    'bunny' => [
        // storage zone name + access key (Bunny Storage → FTP credentials)
        'zone' => env('BUNNY_STORAGE_ZONE'),
        'key' => env('BUNNY_STORAGE_KEY'),
        // storage endpoint hostname (Stockholm default; см. dashboard)
        'host' => env('BUNNY_STORAGE_HOST', 'storage.bunnycdn.com'),
        // публичный CDN pull-zone URL без trailing slash, напр. https://cdn.vivadzen.cz
        'pull_zone' => env('BUNNY_PULL_ZONE'),
    ],

    // Допустимые MIME для загрузки картинок
    'image_mimes' => ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif'],
    'image_max_kb' => env('MEDIA_IMAGE_MAX_KB', 10240),
];
