#!/bin/sh
set -e

# Replace only ${PORT} in nginx config, leave all other $ variables untouched
sed -i "s/\${PORT}/$PORT/g" /etc/nginx/nginx.conf.template
cp /etc/nginx/nginx.conf.template /etc/nginx/nginx.conf

# Laravel setup
php artisan config:clear || true
php artisan migrate --force
php artisan storage:link --force || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM
php-fpm -D

# Wait for FPM to be ready
sleep 3

# Start Nginx
nginx -g 'daemon off;'