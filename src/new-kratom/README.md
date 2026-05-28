# new-kratom

Рабочий каталог для нового сайта Vivadzen — **скоростной storefront** с прицелом
на 90–100 Google PageSpeed на мобиле.

```
new-kratom/
├─ app/                    # Laravel 12 + Octane (FrankenPHP worker mode)
├─ ../new-kratom-admin/    # Nuxt 4 SPA админ-панель
├─ vivadzen-design-tz/     # ТЗ на дизайн
└─ kratom-seo-tz/          # ТЗ на SEO
```

## Архитектура

```
┌──────────────────────────────────────────────────────────┐
│  Frontend (Laravel + Octane FrankenPHP, worker mode)     │
│  • Blade SSR + Alpine.js                                 │
│  • Repositories читают ТОЛЬКО Redis (cache-aside)        │
│  • Cart/Session/Cache/Queue → Redis                      │
│  • Order submit → CreateOrderJob → Horizon → MySQL       │
└──────────────────────────────────────────────────────────┘
            ↑ read              ↓ async write
┌────────────────────┐      ┌──────────────────┐
│  Redis projection  │←──── │  Horizon worker  │
│  (product:{slug},  │      │  (orders,images) │
│  catalog:{tx}:...) │      └──────────────────┘
└────────────────────┘                ↓
       ↑ warm via                ┌────────────────┐
       Observers + cmd           │  MySQL (kratom)│
┌────────────────────┐           │  source-of-truth│
│  Admin (Nuxt 4)    │──→ /admin-api──→ Laravel ──→ writes MySQL + warms Redis
└────────────────────┘
```

**Ключевые компоненты:**

| Слой | Где | Назначение |
|---|---|---|
| `App\Repositories\Redis*Repository` | `app/app/Repositories/` | Чтение каталога из Redis (без fallback в MySQL) |
| `App\Services\CacheWarmer` | `app/app/Services/` | Полная и точечная заливка Redis-проекции |
| `App\Observers\ProductObserver` | `app/app/Observers/` | Write-through: при сохранении продукта в админке обновляется Redis |
| `App\Jobs\CreateOrderJob` | `app/app/Jobs/` | Асинхронное создание заказа из чекаута |
| `App\Services\ImageOptimizer` | `app/app/Services/` | Генерация AVIF/WebP/JPEG в 3 размерах |
| `routes/admin.php` | `app/routes/` | Admin API под `/admin-api/*` (Sanctum tokens) |
| `config/octane.php` + Caddyfile | `app/`, `.docker/dev/new-kratom/` | FrankenPHP worker + Brotli/HTTP/3 |

## Запуск (dev)

Из корня репозитория:

```bash
docker compose -f docker-compose.dev.yml up \
    mysql redis \
    new-kratom new-kratom-vite new-kratom-horizon \
    new-kratom-admin

# Первый раз: создать БД kratom, прогнать миграции и сидеры
docker compose -f docker-compose.dev.yml exec new-kratom \
    php artisan migrate --seed --force
```

После `db:seed` каталог автоматически залит в Redis (`CacheWarmer::warmAll()`),
доступы в админку:

- **Storefront:** http://localhost:8002
- **Admin:** http://localhost:3002  · `admin@vivadzen.cz` / `admin12345`
- **PhpMyAdmin:** http://localhost:8080 (база `kratom`, пользователь `kratom`)
- **Horizon dashboard:** http://localhost:8002/horizon

## Прогрев Redis вручную

```bash
docker compose -f docker-compose.dev.yml exec new-kratom \
    php artisan catalog:warm                # всё
    php artisan catalog:warm --only=products
```

## Почему так быстро

| Источник скорости | Эффект |
|---|---|
| **Octane worker-mode (FrankenPHP)** | Laravel бутстрапится один раз, не на каждый запрос → ~6× меньше TTFB |
| **Все чтения через Redis** | Каталог-страницы: 0 SQL-запросов, чистый `MGET` ~0.2 ms |
| **AVIF (60 q) + WebP fallback** | LCP-изображения весят 30–60% от JPEG, без потери качества |
| **Async fonts + preconnect** | Google Fonts не блокирует FCP |
| **Brotli (Caddy)** | HTML/JS/CSS меньше ~25% vs gzip |
| **Immutable cache hashed assets** | Повторные визиты — 0 запросов за статикой |
| **`Cache-Control: max-age=60, stale-while-revalidate=600`** | CDN/браузер обновляют HTML в фоне |
| **Order через Horizon** | Чекаут возвращает страницу успеха мгновенно, не ждёт `INSERT` |

## Production

В prod-режиме нужно отдельно:

1. Добавить prod-Dockerfile'ы для `new-kratom`, `new-kratom-horizon`, `new-kratom-admin` (по аналогии с dev — в `.docker/prod/`).
2. В `php.ini`: `opcache.validate_timestamps=0`, `opcache.preload=/var/www/html/storage/app/preload.php`.
3. В FrankenPHP Caddyfile убрать `auto_https off`, добавить домен → автоматический Let's Encrypt.
4. `npm run build` для Vite (статика в `/public/build`).
5. Nuxt admin: `npm run build` → SPA в `.output/public`, отдавать как статику.
6. Включить query cache в `config/database.php` если будут тяжёлые админ-запросы.
