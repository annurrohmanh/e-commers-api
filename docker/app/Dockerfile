FROM php:8.5-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    autoconf \
    g++ \
    make \
    git \
    unzip \
    linux-headers \
    bash

RUN git config --system --add safe.directory /var/www/html

RUN docker-php-ext-install pdo pdo_pgsql

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Hanya copy composer dulu supaya layer cache kepakai
COPY composer.json composer.lock* ./

RUN composer install \
    --prefer-dist \
    --no-interaction \
    --no-scripts || true

# Entrypoint sederhana
CMD sh -c "composer install && php artisan serve --host=0.0.0.0 --port=8000"