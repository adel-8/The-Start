#!/bin/sh
set -e

# Laravel optimizations
php artisan config:clear || true
php artisan migrate --force || true
php artisan storage:link --force || true
php artisan config:cache || true
php artisan route:cache || true

# Start queue worker in background
php artisan queue:work --tries=3 --sleep=3 > /dev/null 2>&1 &

# Start php-fpm in background
php-fpm -D

# Substitute port and start nginx in foreground (keeps container alive)
PORT=${PORT:-8080}
envsubst '$PORT' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf
nginx -g 'daemon off;'