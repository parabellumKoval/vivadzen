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
COPY .docker/dev/new-kratom/ensure-composer-install.sh /usr/local/bin/ensure-composer-install
RUN chmod +x /usr/local/bin/ensure-composer-install

WORKDIR /var/www/html

CMD ["sh", "-lc", "\
  target_hash=$(sha1sum composer.lock | awk '{print $1}'); \
  waited=0; \
  while [ $waited -lt 120 ]; do \
    if [ -f vendor/autoload.php ] && [ -f vendor/.composer.lock.sha1 ] && [ \"$(cat vendor/.composer.lock.sha1)\" = \"$target_hash\" ]; then \
      php artisan horizon; \
      exit $?; \
    fi; \
    sleep 1; \
    waited=$((waited + 1)); \
  done; \
  ensure-composer-install; \
  php artisan horizon \
"]
