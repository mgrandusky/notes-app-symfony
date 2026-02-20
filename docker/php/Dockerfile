FROM php:8.2-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        postgresql-dev \
        sqlite-dev \
        zlib-dev \
    && docker-php-ext-install \
        intl \
        opcache \
        pdo_pgsql \
        pdo_sqlite \
        zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
