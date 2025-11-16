<?php

return [
  'settings' => [
    // Драйвер перевода по-умолчанию
    'driver' => env('TRANSLATION_DRIVER', 'libretranslate'),

    'from_languages' => ['cz'],
    'to_languages' => ['en'],

    /* 
    / Перезаписывать существующие строки
    / true - будут перезаписаны даже заполненные переводами строки
    / false - если перевод на каком-то языке присутсвует, он не будет перезаписан 
    */
    'force' => true,
  ],

  // Список доступных драйверов
  // 'drivers' => [
  //   'deepl',
  //   'libretranslate'
  // ],
  'drivers' => [

    'deepl' => [
      'name' => 'DeepL',
      'api_key' => env('DEEPL_API_KEY'),
      'adapter' => 'Dress\Translator\app\Adapters\DeepLTranslator',
      'attempts_amount' => 5
    ],
    
    // 'libretranslate' => [
    //   'name' => 'Libretranslate',
    //   'api_url' => env('LIBRETRANSLATE_URL', 'http://localhost:5000/translate'),
    //   'adapter' => 'Dress\Translator\app\Adapters\LibreTranslator',
    //   'attempts_amount' => 5
    // ]
  ],

  'languages' => [
    'ru' => 'Russain',
    'uk' => 'Ukrainian',
    'en' => 'English',
    'cs' => 'Czech',
    'de' => 'Germany',
    'es' => 'Spain',
  ],

  'history' => [
    'enabled' => true,
    'drivers' => ['logs', 'database', 'console']
  ],
  
  'deepl' => [
      'api_key' => env('DEEPL_API_KEY'),
  ],
  
  'libretranslate' => [
      'api_url' => env('LIBRETRANSLATE_URL', 'http://localhost:5000/translate'),
  ],
];
