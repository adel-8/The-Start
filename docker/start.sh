#!/bin/sh
set -e

echo "=== PORT is: $PORT ==="

# Replace PORT in nginx config
sed -i "s/\${PORT}/$PORT/g" /etc/nginx/nginx.conf.template
cp /etc/nginx/nginx.conf.template /etc/nginx/nginx.conf

echo "=== nginx.conf after substitution ==="
cat /etc/nginx/nginx.conf

# Laravel setup
php artisan config:clear || true
php artisan migrate --force
php artisan storage:link --force || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM
php-fpm -D
sleep 3

echo "=== Checking if FPM is listening ==="
ss -tlnp

echo "=== Starting nginx ==="
nginx -t && nginx -g 'daemon off;'