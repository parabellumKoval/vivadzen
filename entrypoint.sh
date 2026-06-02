#!/usr/bin/env bash
set -e

# Ждём БД (если нужна). DB_HOST/DB_PORT приходят из окружения docker-compose.
if [ -n "${DB_HOST}" ]; then
  echo "Waiting for DB ${DB_HOST}:${DB_PORT:-3306}..."
  for i in {1..60}; do
    (echo > /dev/tcp/${DB_HOST}/${DB_PORT:-3306}) >/dev/null 2>&1 && break || sleep 1
  done
fi

# Опционально: обновить автолоадер (если composer есть в образе)
if command -v composer >/dev/null 2>&1; then
  composer dump-autoload -o || true
fi

# Гарантируем runtime-каталоги Laravel даже если bind-mount storage
# затёр директории, созданные на этапе сборки образа.
mkdir -p \
  storage/app/public \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

chmod 775 \
  storage \
  storage/app \
  storage/app/public \
  storage/framework \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache || true

# Оптимизация Laravel. В новых версиях включает config:cache, route:cache, view:cache (+ events в 11+)
php artisan optimize || true

# Симлинк хранилища (optimize этого не делает)
if [ ! -e "public/storage" ]; then
  php artisan storage:link || true
fi

# (по желанию) миграции
# php artisan migrate --force || true

# ВАЖНО: запускаем то, что передано в CMD (Octane)
exec "$@"
