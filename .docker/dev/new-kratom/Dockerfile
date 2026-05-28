# FrankenPHP — современный PHP application server поверх Caddy.
# Используется Octane runtime (worker mode): Laravel загружается один раз,
# каждый запрос обслуживается из памяти без bootstrap → 5-10× быстрее vs php-fpm.
# Брат-близнец: HTTP/2, HTTP/3, Brotli, статика и автоматический TLS in-box.

FROM dunglas/frankenphp:1-php8.4

ENV SERVER_NAME=":8000" \
    DEBIAN_FRONTEND=noninteractive

# Системные пакеты + расширения, нужные Laravel/Octane/Horizon/Intervention Image
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        zip \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libpng-dev \
        libjpeg-dev \
        libwebp-dev \
        libavif-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        libcurl4-openssl-dev \
        libssl-dev \
        libbrotli-dev \
        nodejs \
        npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-avif \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        exif \
        gd \
        intl \
        mysqli \
        pdo_mysql \
        opcache \
        pcntl \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# PHP конфиг для prod-стиля (большой OPcache, jit on, без validate_timestamps в prod-режиме можно)
COPY .docker/dev/new-kratom/php.ini /usr/local/etc/php/php.ini
COPY .docker/dev/new-kratom/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini

# Caddyfile с Brotli + gzip + static cache headers
COPY .docker/dev/new-kratom/Caddyfile /etc/caddy/Caddyfile

WORKDIR /var/www/html

EXPOSE 8000 443

# Octane в worker-mode под FrankenPHP. Дев-режим: --watch для авто-релоада.
CMD ["sh", "-lc", "\
  [ -f .env ] || cp .env.example .env; \
  [ -f vendor/autoload.php ] || composer install --no-interaction --prefer-dist; \
  grep -Eq '^APP_KEY=base64:' .env || php artisan key:generate --ansi; \
  php artisan migrate --force --graceful; \
  php artisan octane:frankenphp --host=0.0.0.0 --port=8000 --workers=auto --max-requests=500 \
"]
