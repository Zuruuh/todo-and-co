ARG VERSION

FROM composer:2.1.12
FROM php:${VERSION}-fpm-alpine

ENV APP_ENV=prod

RUN mkdir -p /app
#VOLUME /app
WORKDIR /app

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN apk update && \
    apk add --no-cache \
    ca-certificates \
    icu-libs \
    git \
    unzip \
    libzip-dev \
    libpq \
    zlib-dev && \
    apk add postgresql-dev=9.6.10-r0 --repository=http://dl-cdn.alpinelinux.org/alpine/v3.5/main

## Installing building dependencies
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev && \
    docker-php-ext-install \
    intl \
    zip

## Installing php extensions
RUN pecl install apcu-5.1.17 && \
    docker-php-ext-install opcache && \
    docker-php-ext-install pdo && \
    docker-php-ext-install pdo_pgsql && \
    docker-php-ext-install pgsql && \
    docker-php-ext-install zip

## Configuring php & symfony
RUN echo "short_open_tag = off" >> /usr/local/etc/php/php.ini && \
    echo "date.timezone = Europe/Paris" >> /usr/local/etc/php/conf.d/symfony.ini && \
    echo "opcache.max_accelerated_files = 20000" >> /usr/local/etc/php/conf.d/symfony.ini && \
    echo "realpath_cache_size=4096K" >> /usr/local/etc/php/conf.d/symfony.ini && \
    echo "realpath_cache_ttl=600" >> /usr/local/etc/php/conf.d/symfony.ini

COPY ./docker/prod/php/default.ini /usr/local/etc/php/conf.d/default.ini
COPY . /app

RUN composer install --no-dev --no-interaction --optimize-autoloader
RUN apk add gosu --update --no-cache --repository http://dl-3.alpinelinux.org/alpine/edge/testing/ --allow-untrusted && \
    addgroup bar && \
    adduser -D -h /home -s /bin/sh -G bar foo

#RUN rm /usr/local/bin/composer && \
RUN apk del .build-deps

CMD [ "php-fpm" ]