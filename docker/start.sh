#!/bin/sh

php artisan config:clear || true
php artisan migrate --force || true
php artisan db:seed --force || true
php artisan storage:link --force || true
php artisan config:cache || true
php artisan route:cache || true

echo "=== Starting on port 8080 ==="
php artisan serve --host=0.0.0.0 --port=8080