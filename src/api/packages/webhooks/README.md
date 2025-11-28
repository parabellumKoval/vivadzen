# parabellumkoval/webhooks

Модуль для управления вебхуками фронтенда из админки Backpack. Поддерживает ручные действия, запуск по событиям домена и расписание.

## Возможности

- 📦 Виджет `/admin/cache-management` с кнопками прогрева разных частей фронтенда.
- ⚙️ Единая структура конфигурации `config/webhooks.php` (юниты, события, расписания).
- 🧩 Event-mode: привязка Laravel событий к вебхукам. Каждый юнит может подписываться на любое количество событий, данные события передаются на фронт.
- 🔁 Placeholder & batching: поддержка URL вида `/api/_categories/refresh/:slug` с автоматической подстановкой slug и объединением массовых изменений в один запрос.
- ⏰ Schedule-mode: запуск выбранных юнитов по cron (например, автообновление списков на главной каждый 30 минут).
- 📊 Мониторинг: статус последнего запуска хранится в кеше (`webhooks.latest.{unit_key}`) и показывается в виджете.
- 🔌 CLI-инструменты: `php artisan frontend:test-connection`, `frontend:test-job-behavior`, `frontend:debug-url` работают с новой конфигурацией.

## Конфигурация

Файл `config/webhooks.php` (можно опубликовать `php artisan vendor:publish --tag=webhooks-config`). Пример юнита:

```php
'units' => [
    'refresh_categories' => [
        'title' => 'Обновить категории',
        'desc' => 'Перегенерация слуг и дерева категорий',
        'url' => [
            '/api/_categories/refresh/slugs',
            '/api/_categories/refresh/list',
        ],
        'button' => 'Обновить категории',
        'icon' => 'la-list-alt',
        'color' => 'btn-info',
        'events' => ['categories.changed'],
    ],
    'refresh_category_slug' => [
        'title' => 'Категория по slug',
        'desc' => 'Срабатывает только по событиям',
        'url' => '/api/_categories/refresh/:slug',
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
        ],
    ],
],
```

Основные секции:

- `units.*.events` — список внутренних событий (keys из `events` блока), на которые реагирует юнит.
- `units.*.schedules` — ключи расписаний (`schedules` блок).
- `events` — маппинг внутренних событий на реальные Laravel события + резолвер полезной нагрузки.
- `schedules` — cron-выражения и список юнитов, которые должны запускаться.

## События

| Key | Laravel event | Резолвер | Что триггерит |
| --- | --- | --- | --- |
| `settings.updated` | `Backpack\Settings\Events\SettingsGroupChanged` | `SettingsGroupChangedResolver` | Сохранение любой группы настроек |
| `currency_rates.updated` | `Backpack\Store\app\Events\CurrencyRateChanged` | `CurrencyRateChangedResolver` | CRUD курсов валют |
| `categories.changed` | `Backpack\Store\app\Events\CategoryChanged` | `CategoryChangedResolver` | Создание/редактирование/удаление категорий |
| `homepage.lists.updated` | `Backpack\Store\app\Events\ProductListChanged` | `ProductListChangedResolver` | Изменения списков товаров |
| `homepage.articles.updated` | `Backpack\Articles\app\Events\ArticleChanged` | `ArticleChangedResolver` | Сохранение/удаление статей |
| `homepage.video_reviews.updated` | `Backpack\Reviews\app\Events\ReviewChanged` | `ReviewChangedResolver` | Видео отзывы |

Любой юнит может подписаться на несколько событий (например, категории реагируют на `categories.changed` и одновременно запускаются CRON-ом).

## Расписания

`schedules` секция описывает cron для автоматического запуска юнитов. Пример:

```php
'schedules' => [
    'homepage-lists-half-hour' => [
        'cron' => '*/30 * * * *',
        'units' => ['refresh_homepage_lists'],
        'description' => 'Обновить списки каждые 30 минут',
    ],
],
```

При выполнении `php artisan schedule:run` Laravel вызывает `WebhookDispatcher`, который ставит соответствующие задания в очередь.

## Виджет `/admin/cache-management`

- Использует представление `webhooks::widgets.frontend_cache_refresh`.
- Отображаются только юниты, у которых `visible_in_widget = true`.
- Кнопка вызывает `POST /admin/frontend-cache-refresh` с `unit_key`.
- Статусы подгружаются AJAX-ом каждые 30 секунд (`GET /admin/frontend-cache-refresh/status`).

## CLI и отладка

- `php artisan frontend:test-connection` — проверка всех настроенных вебхуков.
- `php artisan frontend:debug-url /api/_fetcher/homepage-main-lists/refresh` — подробный вывод по одному URL.
- `php artisan frontend:test-job-behavior /api/_fetcher/homepage-main-lists/refresh` — повторяет HTTP-клиент, который использует очередь.
- Логи: `tail -f storage/logs/laravel.log | grep Webhook`.

## Интеграция с фронтендом

Бэкенд всегда отправляет POST-запросы на относительные эндпоинты, дополняя их базовым `FRONT_URL`. В теле:

```json
{
  "timestamp": 1736424474,
  "origin": "event", // manual | schedule
  "unit": "refresh_categories",
  "event": "categories.changed",
  "items": [ {"slug": "odezhda"} ],
  "slugs": ["odezhda","obuv"], // если настроен batch
  "meta": {"schedule": "homepage-lists-half-hour"}
}
```

Ответ фронтенда должен быть JSON со статусом 200. При ошибках статус и тело логируются и отображаются в виджете.
