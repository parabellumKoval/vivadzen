# Build arguments
ARG IMAGE_REGISTRY=dev
ARG IMAGE_TAG=latest

# Image
FROM ${IMAGE_REGISTRY}/api-base:${IMAGE_TAG}

# Update dependencies
# RUN apt-get update \
# # Install Swoole (required by Octane)
#     && pecl install swoole \
#     && docker-php-ext-enable swoole \
# # Install Node (v16 LTS) (required by Octane)
#     && curl -sL https://deb.nodesource.com/setup_16.x | bash - \
#     && apt-get install -y nodejs \
# # Install NPM (required by Octane)
#     && npm install -g npm \
# # Install Chokidar (required by Octane)
#     && npm install --g chokidar \
# # Clean up the apt cache
#     && rm -rf /var/lib/apt/lists/*


RUN apt-get update \
# Install Swoole (required by Octane)
    && pecl install swoole \
    && docker-php-ext-enable swoole \
# Clean up the apt cache
    && rm -rf /var/lib/apt/lists/*

ENV NODE_VERSION=16.17.1
ENV NVM_DIR=/root/.nvm

RUN apt install -y curl  && \
    curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash && \
    . "$NVM_DIR/nvm.sh" && nvm install ${NODE_VERSION} && \
    . "$NVM_DIR/nvm.sh" && nvm use v${NODE_VERSION} && \
    . "$NVM_DIR/nvm.sh" && nvm alias default v${NODE_VERSION}
    
ENV PATH="/root/.nvm/versions/node/v${NODE_VERSION}/bin/:${PATH}"

# Specify the node path (allow including Chokidar lib globally)
ENV NODE_PATH /usr/lib/node_modules

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer


ARG MY_USER=sammy
ARG MY_UID=1000
ARG MY_GROUP=staff

# Create system user to run Composer and Artisan Commands
# RUN useradd -G www-data,root -u ${MY_UID} -d /home/${MY_USER} ${MY_USER}
# RUN mkdir -p /home/${MY_USER}/.composer && \
#     chown -R ${MY_USER}:${MY_USER} /home/${MY_USER}

# USER ${MY_USER}

# COPY ./../src/api/storage /var/www/html/storage

# RUN chown -R ${MY_USER}:${MY_USER} /var/www/html && \
#     chmod -R 755 /var/www/html/storage

# Set up the working directory
WORKDIR /var/www/html

# Export Octane port
EXPOSE 8000

# RUN php artisan octane:start --host=0.0.0.0 2>&1 /dev/null || echo "Initial run failed as expected."
# Run the Octane service
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000", "--max-requests=1000", "--watch"]

# The health check configuration
HEALTHCHECK --start-period=5s --interval=5s --timeout=5s --retries=3 \
    CMD php artisan octane:status || exit 1
