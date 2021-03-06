FROM php:7.4-fpm-alpine AS build

LABEL mantainer="developer@fabriziocafolla.com"
LABEL description="Production container"

ARG ENV
ARG APPNAME
ARG DOMAIN 
ARG WORKDIR_USER="www-data"
ARG WORKDIR_GROUP="www-data"
ARG WORKDIRPATH="/var/www"

RUN test -n "${ENV}" || (echo "[BUILD ARG] ENV not set" && false) && \
    test -n "${APPNAME}" || (echo "[BUILD ARG] APPNAME not set" && false) && \
    test -n "${DOMAIN}" || (echo "[BUILD ARG] DOMAIN not set" && false)

ENV build_deps \
		autoconf \
        libzip-dev \
        curl-dev \
        oniguruma-dev \
        zlib-dev

ENV persistent_deps \
		build-base \
        git \
		unzip \
        curl \
        g++ \
        gcc \
        make \
        rsync \
        openssl \
        acl \
        openrc \
        bash \
        libzip \
        zlib 

# Set working directory as
WORKDIR /var/www

# Install build dependencies
RUN apk upgrade --update-cache --available && apk update && \
    apk add --no-cache --virtual .build-dependencies $build_deps

# Install persistent dependencies
RUN apk add --update --no-cache --virtual .persistent-dependencies $persistent_deps && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install docker ext and remove build deps
RUN apk update \
    && docker-php-ext-configure zip \
    && docker-php-ext-install mysqli \
        pdo \
        pdo_mysql \
        bcmath \
        curl \
        pcntl \
        zip \
        exif \
    && apk del -f .build-dependencies

# Install nginx webserver
ARG NGINX_VERSION="1.20.1-r3"
RUN apk add --update --no-cache nginx==$NGINX_VERSION

COPY ./container/etc/nginx /etc/nginx
COPY ./container/etc/php /usr/local/etc
COPY ./container/sbin /usr/local/sbin

ENV ENV ${ENV}
ENV APPNAME ${APPNAME}
ENV DOMAIN ${DOMAIN}
ENV WORKDIR_USER ${WORKDIR_USER}
ENV WORKDIR_GROUP ${WORKDIR_GROUP}
ENV WORKDIRPATH ${WORKDIRPATH}
ENV NGINX_VERSION ${NGINX_VERSION}

RUN chmod 754 -R /usr/local/sbin \
    && setfacl -R -m g:www-data:rwx /usr/local/sbin \
    && /usr/local/sbin/setup/nginx.sh \ 
    && /usr/local/sbin/setup/workdir.sh \
    && /usr/local/sbin/setup/create_cert.sh 

USER www-data

ENTRYPOINT ["/usr/local/sbin/services/init.sh"]

# Dev
FROM build as dev

USER root

RUN apk update && apk upgrade && \
    apk add mysql-client    

# Build app
FROM build as app

COPY --chown=www-data:www-data ./source /var/www

RUN composer install --no-dev --no-scripts --no-suggest --no-interaction --prefer-dist --optimize-autoloader \
    && composer dump-autoload --no-dev --optimize --classmap-authoritative

# Production
FROM build as pro
COPY --from=app /var/www /var/www/

# Staging
FROM build as sta
COPY --from=app /var/www /var/www/