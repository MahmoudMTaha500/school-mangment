FROM php:8.4-fpm-alpine AS base
RUN apk add --no-cache icu-dev libzip-dev oniguruma-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo_mysql intl zip opcache \
    && pecl install redis \
    && docker-php-ext-enable redis
WORKDIR /var/www/html
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM base AS development
ARG INSTALL_XDEBUG=false
RUN if [ "$INSTALL_XDEBUG" = "true" ]; then pecl install xdebug && docker-php-ext-enable xdebug; fi
CMD ["php-fpm"]

FROM base AS production
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
COPY . .
RUN chown -R www-data:www-data storage bootstrap/cache
CMD ["php-fpm"]
