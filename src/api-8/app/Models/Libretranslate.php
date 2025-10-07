<?php

namespace App\Models;

use Illuminate\Support\Facades\Http;

class Libretranslate
{
    static function translate($text, $source = 'ru', $target = 'en')
    {
      try {
          // Отправляем POST-запрос к LibreTranslate
          $response = Http::post(config('services.libretranslate.url'), [
              'q' => $text,
              'source' => $source,
              'target' => $target,
          ]);

          // Проверяем, успешен ли запрос (статус 200)
          if ($response->successful()) {
              // Получаем переведенный текст из JSON-ответа
              $translatedText = $response->json()['translatedText'];

              // Возвращаем результат
              return [
                  'success' => true,
                  'translated' => $translatedText,
              ];
          } else {
              // Если запрос не удался, возвращаем ошибку
              return [
                  'success' => false,
                  'error' => 'Translation failed',
                  'status' => $response->status(),
                  'details' => $response->body(),
              ];
          }
      } catch (\Exception $e) {
          // Обрабатываем исключения (например, если сервис недоступен)
          return [
              'success' => false,
              'error' => 'Unable to connect to translation service',
              'message' => $e->getMessage(),
          ];
      }
    }
}