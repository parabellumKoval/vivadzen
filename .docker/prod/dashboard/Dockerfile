# Build arguments
ARG IMAGE_REGISTRY=prod
ARG IMAGE_TAG=latest

# Base image
FROM ${IMAGE_REGISTRY}/api-base:${IMAGE_TAG} as base

# Serving image
FROM php:8.2-fpm

# Update dependencies
RUN apt-get update \
# Install Zip
    && apt-get install -y libzip-dev zip && docker-php-ext-install zip \
# Install Git
    && apt-get install -y git \
# Install Curl
    && apt-get install -y libcurl3-dev curl && docker-php-ext-install curl \
# Install EXIF
    && docker-php-ext-install exif \
# Install GD
    && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-freetype=/usr/include/ && docker-php-ext-install gd \
# Install MySql
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && apt-get install -y default-mysql-client \
# Install BC Math
    && docker-php-ext-install bcmath \
# Install internationalization functions
    && apt-get install -y zlib1g-dev libicu-dev g++ \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
# Install Redis extension
    && pecl install redis \
    && docker-php-ext-enable redis \
# Install Process Control extension
    && docker-php-ext-install pcntl \
    && docker-php-ext-enable pcntl \
# Install OPcache extension
    && docker-php-ext-install opcache \
# Install Xdebug
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
# Clean up the apt cache
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copy PHP configurations
COPY --from=base "/${PHP_INI_DIR}/php.ini" "/${PHP_INI_DIR}/php.ini"
COPY --from=base "/${PHP_INI_DIR}/conf.d" "/${PHP_INI_DIR}/conf.d"

# Copy PHP-FPM configuration
COPY ./.docker/prod/dashboard/php-fpm.d /usr/local/etc/php-fpm.d

# Set up the working directory
WORKDIR /var/www/html

# Copy files from base image
COPY --from=base /var/www/html ./

# Mysql ssl
RUN sed -i 's/providers = provider_sect/providers = provider_sect\n\
ssl_conf = ssl_sect\n\
\n\
[ssl_sect]\n\
system_default = system_default_sect\n\
\n\
[system_default_sect]\n\
MinProtocol = TLSv1\n\
CipherString = DEFAULT:@SECLEVEL=1\n\
Options = UnsafeLegacyRenegotiation/' /etc/ssl/openssl.cnf


ARG MY_USER=www-data

RUN chown -R ${MY_USER}:${MY_USER} /var/www/html && \
    chmod -R 755 /var/www/html/storage && \
    chmod -R 755 /var/www/html/public


COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

# Expose port 9000 and start php-fpm server
EXPOSE 9000

CMD ["php-fpm"]