FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libxml2-dev \
    libcurl4-openssl-dev libzip-dev libintl-dev \
    libonig-dev && \
    docker-php-ext-install pdo pdo_mysql mbstring \
    dom curl zip gd bcmath intl tokenizer fileinfo opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy project
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

EXPOSE $PORT

CMD php artisan migrate --force && \
    php artisan storage:link && \
    php -S 0.0.0.0:$PORT -t public