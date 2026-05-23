#!/bin/sh

echo "=== PORT is: $PORT ==="

sed -i "s/\${PORT}/$PORT/g" /etc/nginx/nginx.conf.template
cp /etc/nginx/nginx.conf.template /etc/nginx/nginx.conf

php artisan config:clear || true
php artisan migrate --force || true
php artisan storage:link --force || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "=== Starting FPM ==="
php-fpm --allow-to-run-as-root -D
sleep 3

echo "=== FPM check ==="
ss -tlnp | grep 9000 || echo "FPM NOT LISTENING"

echo "=== Kill any existing nginx ==="
pkill nginx || true
sleep 1

echo "=== Nginx config test ==="
nginx -t 2>&1

echo "=== Starting Nginx ==="
nginx -g 'daemon off;' 2>&1
echo "=== Nginx exited: $? ==="

sleep infinity