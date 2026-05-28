# Vivadzen — new-kratom admin (Nuxt 4)

Лёгкая SPA-админка для управления продуктами, таксономиями, заказами и медиа.
Ходит в Laravel admin API на `http://localhost:8002/admin-api`.

## Логин по умолчанию (после `php artisan db:seed`)

- email: `admin@vivadzen.cz`
- password: `admin12345`

## Запуск (через docker-compose.dev)

```bash
docker compose -f docker-compose.dev.yml up new-kratom-admin
# UI: http://localhost:3002
```

## Локально (без Docker)

```bash
npm install
NUXT_PUBLIC_API_BASE=http://localhost:8002/admin-api npm run dev
```
