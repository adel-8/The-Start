#!/bin/sh

php artisan config:clear || true
php artisan migrate --force || true
php artisan storage:link --force || true
php artisan config:cache || true
php artisan route:cache || true

PORT=${PORT:-8080}
envsubst '$PORT' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

echo "=== Starting php-fpm and nginx on port ${PORT} ==="
php-fpm -D
nginx -g 'daemon off;'