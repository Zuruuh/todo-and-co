FROM composer

FROM php:8.0-fpm-alpine

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

## Installing mandatory dependencies
RUN set -ex && \
    apk add --no-cache \
    ca-certificates \
    icu-libs \
    git \
    unzip \
    libzip-dev \
    libpq \
    zlib-dev && \
    apk add postgresql-dev=9.6.10-r0 --repository=http://dl-cdn.alpinelinux.org/alpine/v3.5/main

## Installing temporary dependencies
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev && \
    docker-php-ext-install \
    intl \
    zip

## Installing apcu
RUN pecl install apcu-5.1.17 && docker-php-ext-enable apcu

## Installing xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug 

## Installing required php extensions
RUN docker-php-ext-enable opcache && \
    docker-php-ext-install zip && \
    docker-php-ext-install pdo && \
    docker-php-ext-install pdo_pgsql && \
    docker-php-ext-install pgsql

## Removing temporary dependencies
RUN apk del .build-deps

## Using gosu for permissions management
RUN apk add gosu --update --no-cache --repository http://dl-3.alpinelinux.org/alpine/edge/testing/ --allow-untrusted && \
    addgroup bar && \
    adduser -D -h /home -s /bin/sh -G bar foo

## Custom PHP & Symfony config
RUN echo "error_reporting=E_ALL" > /usr/local/etc/php/conf.d/error_reporting.ini

## Installing blackfire
RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && architecture=$(uname -m) \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/$architecture/$version \
    && mkdir -p /tmp/blackfire \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8307\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
    && rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz

ADD entrypoint.sh /entrypoint

ENTRYPOINT ["/entrypoint"]
