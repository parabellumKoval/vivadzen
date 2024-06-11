#!/bin/sh

certbot renew \
    --non-interactive \
    --webroot \
    --webroot-path /var/www/acme \
    --post-hook /scripts/nginx-reload.sh \
    --cert-name djini.com.ua \
    --force-renewal \
    --no-random-sleep-on-renew \
    --staging
    # --config-dir /etc/nginx/ssl