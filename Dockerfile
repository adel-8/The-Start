FROM php:8.2-fpm

# Install system dependencies
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

# Create PHP-FPM pool configuration using a Unix socket
RUN mkdir -p /var/run/php && \
    echo "[www]" > /usr/local/etc/php-fpm.d/www.conf && \
    echo "user = www-data" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "group = www-data" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "listen = /var/run/php/php8.2-fpm.sock" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "listen.owner = www-data" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "listen.group = www-data" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "listen.mode = 0660" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm = dynamic" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.max_children = 5" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.start_servers = 2" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.min_spare_servers = 1" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.max_spare_servers = 3" >> /usr/local/etc/php-fpm.d/www.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Run package discovery
RUN php artisan package:discover --ansi || true

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache && \
    chown www-data:www-data /var/run/php

# Copy nginx config template
COPY docker/nginx.conf /etc/nginx/nginx.conf.template

EXPOSE 8080

CMD ["sh", "-c", "\
    envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf && \
    php artisan config:clear || true && \
    php artisan migrate --force && \
    php artisan storage:link --force || true && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php-fpm -D && \
    sleep 2 && \
    nginx -g 'daemon off;'"]