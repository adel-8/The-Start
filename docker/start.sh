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

# Fix permissions for all app files
chmod -R 755 /var/www/public

# Start FPM
php-fpm --allow-to-run-as-root -D
sleep 3

ss -tlnp

# Test a direct PHP-FPM connection
echo "=== Testing FPM connection ==="
curl -v http://127.0.0.1:9000 2>&1 | head -20

nginx -t && nginx -g 'daemon off;'