<?php

return [
    // Провайдер по умолчанию: 'local' или 'bunny'
    'default_provider'    => env('IMAGEUPLOAD_DEFAULT_PROVIDER', 'local'),

    // Настройки локального хранилища
    'local_disk'          => env('IMAGEUPLOAD_LOCAL_DISK', 'images'), // название диска из config/filesystems.php
    'local_url_prefix'    => env('IMAGEUPLOAD_LOCAL_URL_PREFIX', env('APP_URL')),
    // (local_url_prefix указывает базовый URL для доступа к файлам на диске; 
    // обычно это APP_URL/storage для публичного диска, если настроен symbolic link)

    // Настройки BunnyCDN
    'bunny_storage_zone'  => env('BUNNY_STORAGE_ZONE', ''),    // имя Storage Zone на BunnyCDN
    'bunny_api_key'       => env('BUNNY_API_KEY', ''),         // API-ключ (Password) для Storage Zone
    'bunny_region'        => env('BUNNY_REGION', 'de'),        // код региона ('de' для Европы (DE), 'ny' для Нью-Йорка, 'sg' для Сингапура, и т.д.)
    'bunny_root_folder'   => env('BUNNY_ROOT_FOLDER', ''),     // корневая папка в Storage Zone (если нужна, иначе пусто)
    'bunny_pull_zone_url' => env('BUNNY_PULL_ZONE_URL', ''),   // URL вашего Pull Zone (например, https://{ваш-пуллзон}.b-cdn.net)

    // Опции именования файлов
    'preserve_original_name' => env('IMAGEUPLOAD_PRESERVE_ORIGINAL', false),  // сохранять оригинальное имя файла
    'generate_unique_name'   => env('IMAGEUPLOAD_GENERATE_UNIQUE', true),   // генерировать уникальное имя (если true, имеет приоритет над оригинальным)

    // Папка по умолчанию для сохранения (относительно корня диска или Storage Zone)
    'default_folder'      => env('IMAGEUPLOAD_DEFAULT_FOLDER', 'images'),

    // Канал логирования
    'logging_channel'     => env('IMAGEUPLOAD_LOGGING_CHANNEL', 'imageupload'),
];
