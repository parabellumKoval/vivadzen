# Build arguments
ARG IMAGE_REGISTRY=prod
ARG IMAGE_TAG=latest

# Image
FROM ${IMAGE_REGISTRY}/api-base:${IMAGE_TAG}

# Update dependencies
RUN apt-get update \
# Install Swoole (required by Octane)
    && pecl install swoole \
    && docker-php-ext-enable swoole \
# Clean up the apt cache
    && rm -rf /var/lib/apt/lists/*



COPY ./src/api/.env.prod ./.env

# Dump composer autoload
# RUN composer dump-autoload --optimize

# RUN php artisan optimize
# RUN php artisan optimize \
#     && php artisan storage:link

# Set up the working directory
WORKDIR /var/www/html

COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

# Export Octane port
EXPOSE 8000

# Run the Octane service
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000", "--max-requests=500"]

# The health check configuration
HEALTHCHECK --start-period=5s --interval=5s --timeout=5s --retries=3 \
    CMD php artisan octane:status || exit 1