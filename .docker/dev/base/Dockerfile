# Image
FROM php:8.2-cli

# Update dependencies
RUN apt-get update \
# WebP
    && apt-get install -y libwebp-dev \
# Install Zip
    && apt-get install -y libzip-dev zip \
    && docker-php-ext-install zip \
# Install Git
    && apt-get install -y git \
# Install Curl
    && apt-get install -y libcurl4-openssl-dev curl \
    && docker-php-ext-install curl \
# Install procps (required by Octane)
    && apt-get install -y procps \
# Install EXIF
    && docker-php-ext-install exif \
# Install GD
    && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-freetype=/usr/include/ --with-webp \
    && docker-php-ext-install gd \
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
# Clean up the apt cache
    && rm -rf /var/lib/apt/lists/*

# Copy PHP configuration
COPY ./.docker/dev/base/php.ini "/${PHP_INI_DIR}/php.ini"
COPY ./.docker/dev/base/conf.d "/${PHP_INI_DIR}/conf.d"

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