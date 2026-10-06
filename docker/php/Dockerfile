FROM php:8.3-fpm-alpine

# unzip is needed by Composer to unpack downloaded packages
RUN apk add --no-cache unzip \
    && docker-php-ext-install pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
