<?php

return [
  // Полное имя класса провайдера курсов
  'provider' => \Backpack\Store\app\Services\Currency\Providers\NbuExchangeRateProvider::class,

  // Список валют, которые нужно загружать (опционально; пустой = все доступные у источника)
  'symbols' => ['USD','EUR','UAH','CZK'],

  // Ключ в кэше и store
  'cache_key' => 'store:currency:rates:v1',
  'cache_store' => env('STORE_CURRENCY_CACHE_STORE', null), // null = default
  'cache_ttl_seconds' => 0, // 0 или null = forever

  // Разрешить фоновую подзагрузку при отсутствии кэша (лучше false для предсказуемости)
  'lazy_fetch_when_missing' => true,

  // Включить планировщик команды обновления внутри пакета (можно выключить и расписать в приложении)
  'schedule' => [
      'enabled' => true,
      'cron' => '0 3 * * *', // каждый день в 03:00
  ],
];