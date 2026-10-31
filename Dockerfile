FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM composer:2 AS dependencies

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

FROM php:8.4-fpm-alpine AS app

RUN apk add --no-cache libpq libzip libxml2 oniguruma \
    && apk add --no-cache --virtual .build-dependencies $PHPIZE_DEPS postgresql-dev libzip-dev libxml2-dev oniguruma-dev \
    && docker-php-ext-install dom mbstring opcache pcntl pdo_pgsql xml xmlreader xmlwriter zip \
    && apk del .build-dependencies

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=dependencies --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build
COPY --chown=www-data:www-data docker/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/fintec-entrypoint
COPY docker/php-production.ini /usr/local/etc/php/conf.d/zz-production.ini

RUN mkdir -p storage/app/private storage/app/public storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

ENTRYPOINT ["/usr/local/bin/fintec-entrypoint"]
CMD ["php-fpm", "-F"]

FROM nginxinc/nginx-unprivileged:alpine AS web

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public

