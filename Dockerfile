FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    nginx gettext-base curl \
    libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev libicu-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl xml

# PHP-FPM config: listen explicitly on 127.0.0.1:9000
RUN echo "[www]" > /usr/local/etc/php-fpm.d/www.conf && \
    echo "user = www-data" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "group = www-data" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "listen = 127.0.0.1:9000" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm = dynamic" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.max_children = 5" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.start_servers = 2" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.min_spare_servers = 1" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.max_spare_servers = 3" >> /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-scripts && \
    php artisan package:discover --ansi || true

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Use a startup script instead of inline CMD
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

COPY docker/nginx.conf /etc/nginx/nginx.conf.template

EXPOSE 8080

CMD ["/start.sh"]