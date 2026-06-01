#!/bin/sh

# Prepare the application (non-fatal failures are tolerated so the container still starts)
php artisan config:clear || true
php artisan migrate --force || true
php artisan storage:link --force || true
php artisan config:cache || true
php artisan route:cache || true

# NOTE: Seeders should NOT run automatically in production on every container start.
# Run seeders manually and only once when needed, e.g.:
#   php artisan db:seed --class=RolesTableSeeder

# Ensure PORT is set (default to 8080)
: ${PORT:=8080}
export PORT

# Render nginx config from template (substitutes ${PORT})
if [ -f /etc/nginx/nginx.conf.template ]; then
	envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf
fi

# Start php-fpm as a background daemon (so nginx can connect to it on 127.0.0.1:9000)
if command -v php-fpm >/dev/null 2>&1; then
	php-fpm -D || true
elif command -v php-fpm8.2 >/dev/null 2>&1; then
	php-fpm8.2 -D || true
fi

# Start the Laravel queue worker in the background so queued mails/jobs are processed
# Using --daemon as requested; consider using a process supervisor in production.
php artisan queue:work --daemon --tries=3 --sleep=3 &

echo "=== Starting nginx (port=${PORT}) ==="

# Start nginx in the foreground so it becomes PID 1 for the container
exec nginx -g 'daemon off;'