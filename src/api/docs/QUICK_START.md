# Краткая инструкция по запуску Frontend Cache Refresh Widget

## Что было создано:

1. **Конфигурация**: `config/frontend_cache_refresh.php`
2. **Job**: `app/Jobs/FrontendCacheRefreshJob.php`
3. **Контроллер**: `app/Http/Controllers/Admin/FrontendCacheRefreshController.php`
4. **Маршруты**: `routes/admin/frontend_cache_refresh.php`
5. **Виджет**: `resources/views/vendor/backpack/base/widgets/frontend_cache_refresh.blade.php`
6. **Dashboard**: `resources/views/vendor/backpack/base/dashboard.blade.php`

## Быстрый старт:

### 1. Обновить autoload
```bash
cd src/api
composer dump-autoload
```

### 2. Очистить кеш
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Проверить что всё работает
- Перейти в админку: `/admin/dashboard`
- Увидеть новый виджет "Frontend Cache Management"
- Попробовать нажать на любую кнопку

### 4. Настройка очередей (опционально)
Если хотите асинхронную обработку:

```bash
# В .env
QUEUE_CONNECTION=database
# или
QUEUE_CONNECTION=redis

# Создать таблицы для очередей (если используете database)
php artisan queue:table
php artisan migrate

# Запустить воркер
php artisan queue:work --queue=frontend-cache
```

### 5. Кастомизация юнитов

Редактировать `config/frontend_cache_refresh.php`:

```php
'units' => [
    // Простой юнит
    [
        'title' => 'Мой кеш',
        'desc' => 'Описание моего кеша', 
        'url' => '/api/_refresh-my-cache',
        'button' => 'Обновить мой кеш',
        'icon' => 'la-cog',
        'color' => 'btn-primary',
    ],
    // Юнит с несколькими URL
    [
        'title' => 'Категории',
        'desc' => 'Обновить все кеши категорий',
        'url' => [
            '/api/_categories/refresh/slugs',
            '/api/_categories/refresh/list',
        ],
        'button' => 'Обновить категории',
        'icon' => 'la-list-alt',
        'color' => 'btn-info',
    ],
    // Юнит с безлимитным timeout
    [
        'title' => 'Тяжелая операция',
        'desc' => 'Долгая операция без ограничений времени',
        'url' => '/api/_heavy-operation',
        'button' => 'Запустить',
        'icon' => 'la-cog',
        'color' => 'btn-warning',
        'timeout' => 0, // безлимитный
    ],
],
```

## Проверка статуса:

### API эндпоинты:
- `GET /admin/frontend-cache-refresh/status` - статус всех юнитов
- `POST /admin/frontend-cache-refresh` - запуск обновления

### Логи:
```bash
tail -f storage/logs/laravel.log | grep "Frontend cache refresh"
```

## Требования к фронтенду:

Фронтенд должен принимать POST запросы на указанные URL, например:
- `http://localhost:3000/api/_refresh-settings`
- `http://localhost:3000/api/_refresh-categories`

Пример response от фронтенда:
```json
{
  "success": true,
  "message": "Cache refreshed successfully"
}
```

## 🐳 Настройка для Docker:

Если ваше Laravel приложение запущено в Docker контейнере, а фронтенд снаружи:

### 1. Обновите .env:
```env
# Вместо localhost используйте host.docker.internal
FRONT_URL=http://host.docker.internal:3000/
```

### 2. Альтернативные варианты для .env:
```env
# Вариант 1: host.docker.internal (рекомендуется для Docker Desktop)
FRONT_URL=http://host.docker.internal:3000/

# Вариант 2: IP адрес хост машины (для Linux Docker)
FRONT_URL=http://172.17.0.1:3000/

# Вариант 3: реальный IP адрес вашей машины
FRONT_URL=http://192.168.1.100:3000/
```

### 3. Проверка сети Docker:
```bash
# Проверить доступность фронтенда изнутри контейнера
docker exec -it <container_name> curl http://host.docker.internal:3000/api/_refresh-settings

# Или войти в контейнер и проверить
docker exec -it <container_name> bash
curl http://host.docker.internal:3000/api/_refresh-settings
```

### 4. Docker Compose сеть:
Если используете docker-compose, убедитесь что есть доступ к внешней сети:
```yaml
# docker-compose.yml
services:
  app:
    # ... other config
    extra_hosts:
      - "host.docker.internal:host-gateway"
```

### 5. Тестирование подключения:
```bash
# Протестировать все настроенные эндпоинты
php artisan frontend:test-connection

# Протестировать конкретный URL
php artisan frontend:test-connection --url=http://host.docker.internal:3000/api/_refresh-settings

# С кастомным таймаутом
php artisan frontend:test-connection --timeout=30
```