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

ss -tlnp

nginx -t && nginx -g 'daemon off;' &
sleep 5

echo "=== Making test request ==="
curl -v http://127.0.0.1:$PORT 2>&1

echo "=== Nginx error log ==="
cat /var/log/nginx/error.log

# Keep container alive
tail -f /var/log/nginx/error.log