# Frontend Cache Refresh Widget для Laravel Backpack

Этот виджет позволяет обновлять кеш на фронтенде напрямую из админ панели Laravel Backpack.

## Установка и настройка

### 1. Конфигурация

Файл конфигурации: `config/webhooks.php`

```php
<?php

return [
    'units' => [
        [
            'title' => 'Settings Cache',
            'desc' => 'Refresh application settings cache on frontend',
            'url' => '/api/_refresh-settings',
            'button' => 'Refresh Settings',
            'icon' => 'la-cog',
            'color' => 'btn-primary',
        ],
        // ... другие юниты
    ],
    'frontend_url' => env('FRONT_URL', 'http://localhost:3000/'),
    'timeout' => 30,
    'widget' => [
        'title' => 'Frontend Cache Management',
        'description' => 'Manage and refresh frontend cache from admin panel',
        'grid_columns' => 3,
        'show_last_refresh' => true,
        'show_status' => true,
    ],
];
```

### 2. Настройка .env

Убедитесь что в вашем `.env` файле указан правильный URL фронтенда:

```env
FRONT_URL=http://localhost:3000/
```

### 3. Очереди (рекомендуется)

Для асинхронной обработки запросов настройте очереди:

```env
QUEUE_CONNECTION=redis
# или
QUEUE_CONNECTION=database
```

Если используете `QUEUE_CONNECTION=sync`, все запросы будут выполняться синхронно.

### 4. Запуск воркера очередей

```bash
php artisan queue:work --queue=frontend-cache
```

## Использование

### В админ панели

1. Перейдите на страницу Dashboard (`/admin/dashboard`)
2. Найдите виджет "Frontend Cache Management"
3. Нажмите на нужную кнопку для обновления кеша
4. Следите за статусом выполнения

### API Endpoints

Виджет использует следующие эндпоинты:

- `POST /admin/frontend-cache-refresh` - Запуск обновления кеша
- `GET /admin/frontend-cache-refresh/status` - Получение статуса всех юнитов
- `GET /admin/frontend-cache-refresh/status/{unitUrl}` - Статус конкретного юнита
- `DELETE /admin/frontend-cache-refresh/status` - Очистка кеша статусов

## Добавление новых юнитов

Для добавления нового юнита обновления кеша, добавьте новый элемент в массив `units` в конфигурации:

### Простой юнит (один URL):
```php
[
    'title' => 'My Cache Unit',
    'desc' => 'Description of what this cache unit does',
    'url' => '/api/_refresh-my-cache', // URL на фронтенде
    'button' => 'Refresh My Cache',
    'icon' => 'la-database', // Line Awesome icon class
    'color' => 'btn-success', // Bootstrap button class
],
```

### Юнит с несколькими URL:
```php
[
    'title' => 'Categories Cache', 
    'desc' => 'Refresh all category-related cache',
    'url' => [
        '/api/_categories/refresh/slugs',
        '/api/_categories/refresh/list',
        '/api/_categories/refresh/tree',
    ],
    'button' => 'Refresh Categories',
    'icon' => 'la-list-alt',
    'color' => 'btn-info',
],
```

### Юнит с кастомным timeout:
```php
[
    'title' => 'Heavy Processing',
    'desc' => 'Long-running cache refresh operation',
    'url' => '/api/_heavy-refresh',
    'button' => 'Start Heavy Refresh',
    'icon' => 'la-cog',
    'color' => 'btn-warning',
    'timeout' => 0, // Безлимитный timeout (0 = unlimited)
],
```

### Юнит с обычным timeout:
```php
[
    'title' => 'Quick Refresh',
    'desc' => 'Fast cache refresh with custom timeout',
    'url' => '/api/_quick-refresh', 
    'button' => 'Quick Refresh',
    'icon' => 'la-bolt',
    'color' => 'btn-primary',
    'timeout' => 120, // 2 минуты
],
```

### Доступные цвета кнопок

- `btn-primary` (синий)
- `btn-secondary` (серый)
- `btn-success` (зеленый)
- `btn-danger` (красный)
- `btn-warning` (желтый)
- `btn-info` (голубой)
- `btn-light` (светлый)
- `btn-dark` (темный)

## Настройка timeout

### Глобальный timeout
В файле конфигурации можно установить глобальный timeout для всех операций:

```php
'timeout' => 30, // 30 секунд для всех операций
```

### Индивидуальный timeout для юнитов
Каждый юнит может иметь свой собственный timeout, который переопределяет глобальный:

```php
[
    'title' => 'Quick Operation',
    'url' => '/api/_quick-refresh',
    'timeout' => 10, // 10 секунд
],
[
    'title' => 'Long Operation', 
    'url' => '/api/_long-refresh',
    'timeout' => 300, // 5 минут
],
[
    'title' => 'Unlimited Operation',
    'url' => '/api/_unlimited-refresh', 
    'timeout' => 0, // Безлимитный timeout
],
```

### Особенности безлимитного timeout
- При `timeout => 0` операция может выполняться неограниченно долго
- Внутренний timeout HTTP клиента установлен в 1 час (3600 сек)
- Connect timeout увеличен до 60 секунд
- Retry логика отключена для безлимитных операций

## Множественные URL

### Как это работает
Когда юнит имеет массив URL, система:
1. Последовательно отправляет POST запросы на каждый URL
2. Для каждого URL пробует Docker альтернативы при необходимости
3. Считает операцию успешной только если ВСЕ URL отработали успешно
4. Ведет детальное логирование по каждому URL

### Отображение в интерфейсе
- Виджет показывает количество endpoints: "3 endpoints"
- Статус показывает результат: "Success (3)" или "Failed (3)"
- При timeout = 0 показывается: "Unlimited timeout"

### Логирование
```bash
# Пример лога для юнита с 3 URL
[2025-11-10 10:30:15] Frontend cache refresh job started {"unit":"Categories Cache","unit_urls":["/api/_categories/refresh/slugs","/api/_categories/refresh/list"],"timeout":"unlimited","total_requests":2}
[2025-11-10 10:30:16] Processing unit URL {"unit":"Categories Cache","unit_url":"/api/_categories/refresh/slugs","urls_to_try":["http://host.docker.internal:3000/api/_categories/refresh/slugs"]}
[2025-11-10 10:30:17] Frontend cache refresh URL completed successfully {"unit":"Categories Cache","successful_url":"http://host.docker.internal:3000/api/_categories/refresh/slugs","response_time":1.234}
```

### Доступные цвета кнопок

- `btn-primary` (синий)
- `btn-secondary` (серый)
- `btn-success` (зеленый)
- `btn-danger` (красный)
- `btn-warning` (желтый)
- `btn-info` (голубой)
- `btn-light` (светлый)
- `btn-dark` (темный)

### Иконки

Используйте иконки из Line Awesome (https://icons8.com/line-awesome):
- `la-cog` - настройки
- `la-database` - база данных
- `la-refresh` - обновление
- `la-users` - пользователи
- `la-shopping-cart` - корзина
- `la-list-alt` - список
- `la-trash` - удаление

## Логирование

Все операции логируются в Laravel logs с тегом `frontend-cache-refresh`:

```bash
tail -f storage/logs/laravel.log | grep "frontend-cache-refresh"
```

## Мониторинг

### Статусы операций

- `never_run` - операция никогда не запускалась
- `running` - выполняется в данный момент
- `success` - успешно завершена
- `failed` - завершена с ошибкой

### Кеш статусов

Статусы операций хранятся в кеше Laravel в течение 1 часа. Ключи:
- `webhooks.latest.{unit_key}` - последний статус юнита

## Безопасность

- Все маршруты защищены middleware `admin`
- CSRF токены обязательны для POST запросов
- Валидация входящих данных
- Rate limiting через Laravel

## Troubleshooting

### Проблема: Кнопки не работают

1. Проверьте консоль браузера на наличие JavaScript ошибок
2. Убедитесь что CSRF токен присутствует в meta теге
3. Проверьте правильность маршрутов в `packages/webhooks/routes/admin.php`

### Проблема: Задачи зависают

1. Проверьте статус воркера очередей: `php artisan queue:work --verbose`
2. Убедитесь что фронтенд URL доступен
3. Проверьте таймаут в конфигурации

### Проблема: Нет ответа от фронтенда

1. Убедитесь что `FRONT_URL` правильно настроен
2. Проверьте что эндпоинты на фронтенде принимают POST запросы
3. Проверьте CORS настройки если фронтенд на другом домене

## Разработка

### Добавление новой функциональности

1. Модифицируйте `WebhookDispatchJob` для новой логики
2. Обновите контроллер `WebhookUnitController`
3. Добавьте новые маршруты в `packages/webhooks/routes/admin.php`
4. Обновите виджет в `packages/webhooks/resources/views/widgets/frontend_cache_refresh.blade.php`

### Тестирование

```bash
# Тест конфигурации
php artisan config:cache

# Тест маршрутов
php artisan route:list | grep frontend-cache-refresh

# Тест задач
php artisan queue:work --once --queue=frontend-cache
```
