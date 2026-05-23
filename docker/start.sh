#!/bin/sh
set -e

echo "=== PORT is: $PORT ==="

sed -i "s/\${PORT}/$PORT/g" /etc/nginx/nginx.conf.template
cp /etc/nginx/nginx.conf.template /etc/nginx/nginx.conf

php artisan config:clear || true
php artisan migrate --force
php artisan storage:link --force || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

php-fpm --allow-to-run-as-root -D
sleep 3

nginx -g 'daemon off;'