# @author: ruiorz (ruiorz@qq.com)
# @link https://github.com/ruiorz
FROM php:8.1-cli-alpine3.17

LABEL desc="php8.1 alpine image with composer" author="ruiorz" email="ruiorz@qq.com"

RUN set -ex \
    # enable gd and xdebug
    && echo https://mirrors.aliyun.com/alpine/v3.17/main/ > /etc/apk/repositories \
    && echo https://mirrors.aliyun.com/alpine/v3.17/community/ >> /etc/apk/repositories \
    && apk update \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS tzdata \
    && apk add --no-cache \
    libpng-dev libjpeg-turbo-dev freetype-dev linux-headers \
    && export MAKEFLAGS="-j$(nproc)" \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && pecl install xdebug-3.2.2 && docker-php-ext-enable xdebug \
    && cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime && echo 'Asia/Shanghai' > /etc/timezone \
    && apk del --no-network .build-deps \
    && docker-php-source delete \
    && rm -rf /usr/share/man /var/cache/apk/* /tmp/* \
    # install composer
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . /app