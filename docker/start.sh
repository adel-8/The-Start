#!/bin/sh

echo "=== PORT is: $PORT ==="

php artisan config:clear || true
php artisan migrate --force || true
php artisan storage:link --force || true
php artisan config:cache || true
php artisan route:cache || true

echo "=== Starting Laravel ==="
php artisan serve --host=0.0.0.0 --port=$PORT