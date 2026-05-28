# Horizon worker — обрабатывает фоновые задачи new-kratom: создание заказов, прогрев Redis,
# отправка email/webhooks, генерация AVIF/WebP при загрузке изображений.
# CLI-режим того же PHP-стека что и web-контейнер.

FROM dunglas/frankenphp:1-php8.4

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
        git \
        unzip \
        zip \
        libzip-dev \
        libicu-dev \
        libpng-dev \
        libjpeg-dev \
        libwebp-dev \
        libavif-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-avif \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        intl \
        pdo_mysql \
        opcache \
        pcntl \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

CMD ["sh", "-lc", "\
  [ -f vendor/autoload.php ] || composer install --no-interaction --prefer-dist; \
  php artisan horizon \
"]
