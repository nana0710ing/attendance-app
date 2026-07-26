FROM php:8.1-fpm

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y git zip unzip \
    && docker-php-ext-install pdo_mysql

WORKDIR /var/www