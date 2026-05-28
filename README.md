# vivadzen

## TG Mini App

Dev:

```bash
docker compose -f docker-compose.dev.yml up -d tg
```

Приложение будет доступно на `http://localhost:3001`.

Prod:

```bash
docker compose -f docker-compose.yml up -d tg
```

Прод-контейнер поднимает собранный Nuxt/Nitro на порту `3001`. Для публичного домена можно переопределить `TG_PUBLIC_SITE_URL` и `TG_PUBLIC_API_BASE` в корневом `.env`.

## Horizon

Для мониторинга и обработки Redis-очередей используется отдельный сервис `horizon`.

Dev:

```bash
docker compose -f docker-compose.dev.yml up -d horizon redis mysql meilisearch
```

Prod:

```bash
docker compose -f docker-compose.yml up -d horizon redis mysql meilisearch
```

Интерфейс Horizon доступен по пути `/admin/horizon` на том же домене, где открыт Laravel admin.

Полезные команды:

```bash
make horizon.status
make horizon.terminate
```

Сервис `queue` переведён в профиль `legacy-queue` и по умолчанию не запускается, чтобы не дублировать обработку тех же Redis-очередей вместе с Horizon.
