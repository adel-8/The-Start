FROM php:8.2-fpm

# Install system dependencies (including gettext-base for envsubst)
RUN apt-get update && apt-get install -y \
    nginx \
    gettext-base \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl xml

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies (no dev, no scripts)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Run package discovery (does NOT need .env)
RUN php artisan package:discover --ansi || true

# Set permissions (storage & bootstrap/cache must be writable)
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy nginx config template
COPY docker/nginx.conf /etc/nginx/nginx.conf.template

EXPOSE 8080

# Start command: inject PORT, run migrations & cache, then start services
CMD ["sh", "-c", "\
    envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf && \
    php artisan config:clear && \
    php artisan migrate:fresh --force && \
    php artisan db:seed --force && \
    php artisan storage:link && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php-fpm -D && \
    nginx -g 'daemon off;'"]