<?php

use ParabellumKoval\BackpackImages\Providers\BunnyCdnImageProvider;
use ParabellumKoval\BackpackImages\Providers\LocalImageProvider;

return [
    'default_provider' => env('IMAGEUPLOAD_DEFAULT_PROVIDER', 'local'),
    'default_folder' => env('IMAGEUPLOAD_DEFAULT_FOLDER', 'images'),
    'preserve_original_name' => env('IMAGEUPLOAD_PRESERVE_ORIGINAL', false),
    'generate_unique_name' => env('IMAGEUPLOAD_GENERATE_UNIQUE', true),
    'logging_channel' => env('IMAGEUPLOAD_LOGGING_CHANNEL', 'imageupload'),
    'default_url_prefix' => env('IMAGEUPLOAD_DEFAULT_URL_PREFIX', '/'),

    'providers' => [
        'local' => [
            'driver' => LocalImageProvider::class,
            'disk' => env('IMAGEUPLOAD_LOCAL_DISK', 'images'),
            'url_prefix' => env('IMAGEUPLOAD_LOCAL_URL_PREFIX', env('APP_URL')),
        ],
        'bunny' => [
            'driver' => BunnyCdnImageProvider::class,
            'storage_zone' => env('BUNNY_STORAGE_ZONE', ''),
            'api_key' => env('BUNNY_API_KEY', ''),
            'region' => env('BUNNY_REGION', 'de'),
            'root_folder' => env('BUNNY_ROOT_FOLDER', ''),
            'pull_zone_url' => env('BUNNY_PULL_ZONE_URL', ''),
        ],
    ],
];
