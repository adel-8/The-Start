FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    nginx gettext-base curl net-tools \
    libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev libicu-dev \
    nodejs npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl xml

RUN printf '[www]\nuser = www-data\ngroup = www-data\nlisten = 127.0.0.1:9000\npm = dynamic\npm.max_children = 5\npm.start_servers = 2\npm.min_spare_servers = 1\npm.max_spare_servers = 3\n' > /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-scripts && \
    php artisan package:discover --ansi || true

RUN npm install && npm run build

RUN chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

COPY docker/nginx.conf /etc/nginx/nginx.conf.template

EXPOSE 8080

CMD ["/start.sh"]
