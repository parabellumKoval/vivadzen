# Build arguments
ARG IMAGE_REGISTRY=prod
ARG IMAGE_TAG=latest

# Image
FROM ${IMAGE_REGISTRY}/api-base:${IMAGE_TAG}

# Update dependencies
RUN apt-get update \
# Install Crontab
    && apt-get install -y cron \
# Clean up the apt cache
    && rm -rf /var/lib/apt/lists/*

# Copy and enable cron job file
COPY ./../.docker/prod/schedule/cron.d/crontab /etc/cron.d/crontab

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/crontab

RUN crontab /etc/cron.d/crontab

# Set up the working directory
WORKDIR /var/www/html

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

COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

# Run the cron service
CMD ["cron", "-f"]