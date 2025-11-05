# Build arguments
ARG IMAGE_REGISTRY=prod
ARG IMAGE_TAG=latest

# Image
FROM ${IMAGE_REGISTRY}/api-base:${IMAGE_TAG}

# Set up the working directory
WORKDIR /var/www/html


COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

# Run the queue service
CMD ["php", "artisan", "queue:work", "--tries=3", "--sleep=3"]