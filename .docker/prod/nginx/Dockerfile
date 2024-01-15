# Image
FROM pagespeed/nginx-pagespeed

# Add nginx configurations
COPY ./.docker/prod/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./.docker/prod/nginx/snippets /etc/nginx/snippets
COPY ./.docker/prod/nginx/sites-internal /etc/nginx/sites-internal
COPY ./.docker/prod/nginx/sites-enabled /etc/nginx/sites-enabled
COPY ./.docker/prod/nginx/fastcgi_params /etc/nginx/fastcgi_params

# Copy public folder
COPY ./src/api/public /var/www/html/public

RUN mkdir -p /var/ngx_pagespeed_cache && \
    chown -R nginx:nginx /var/ngx_pagespeed_cache

# Expose a port of the gateway server
EXPOSE 8000

# Expose a port of the status server
EXPOSE 8001