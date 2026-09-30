# syntax=docker/dockerfile:1.7
# Taleed Procurement backend image (decisions.md D-09). Build context: repository root.
#   target `dev`     — local Docker (source bind-mounted, Composer available)
#   target `release` — immutable image: PHP code + vendor + compiled SPA, no dev tools
# Base images are pinned by multi-arch index digest; update deliberately.

ARG PHP_IMAGE=php:8.4-fpm-alpine@sha256:78cd8de9970a9cd6ff4d98860a94eb5bf37dd2d5776b4785562dbfad01c29d5a
ARG COMPOSER_IMAGE=composer:2.8@sha256:5248900ab8b5f7f880c2d62180e40960cd87f60149ec9a1abfd62ac72a02577c
ARG NODE_IMAGE=node:22-alpine@sha256:0a7108bf6c7bf5de370ffb1a3ed6be93d405b43ff159f681a8d18c0e2bc2e402

FROM ${COMPOSER_IMAGE} AS composer

# ---------------------------------------------------------------- php-base
FROM ${PHP_IMAGE} AS php-base
RUN set -eux; \
    apk add --no-cache fcgi icu-libs libzip libpng libjpeg-turbo libwebp freetype; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev linux-headers; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath exif gd intl opcache pcntl zip; \
    apk del .build-deps; \
    php -m | grep -qi pdo_mysql
COPY infra/docker/php/conf.d/procurement.ini /usr/local/etc/php/conf.d/zz-procurement.ini
COPY infra/docker/php/fpm-pool.conf /usr/local/etc/php-fpm.d/zz-procurement.conf
COPY infra/docker/php/healthcheck.sh /usr/local/bin/php-fpm-healthcheck
RUN chmod 0755 /usr/local/bin/php-fpm-healthcheck
WORKDIR /var/www/html
HEALTHCHECK --interval=15s --timeout=5s --retries=5 CMD ["php-fpm-healthcheck"]

# ---------------------------------------------------------------- dev
FROM php-base AS dev
RUN apk add --no-cache git unzip
COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_HOME=/tmp/composer
# Source is bind-mounted from ./backend. OPcache revalidates on every request.
RUN echo "opcache.validate_timestamps=1" > /usr/local/etc/php/conf.d/zzz-dev.ini

# ---------------------------------------------------------------- vendor (release)
FROM composer AS vendor
WORKDIR /app
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist
COPY backend/ ./
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts

# ---------------------------------------------------------------- spa (release)
FROM ${NODE_IMAGE} AS spa
WORKDIR /repo
COPY package.json package-lock.json .npmrc ./
RUN npm ci --ignore-scripts
COPY index.html vite.config.ts tsconfig.json tsconfig.core.json ./
COPY public ./public
COPY src ./src
RUN mkdir -p backend/public && npm run build:backend

# ---------------------------------------------------------------- release
FROM php-base AS release
ENV APP_ENV=production APP_DEBUG=false
COPY --from=vendor --chown=www-data:www-data /app /var/www/html
COPY --from=spa --chown=www-data:www-data /repo/backend/public/spa /var/www/html/public/spa
# Statamic CP assets are published from the locked vendor package (no network).
# A throwaway key and in-memory cache/DB let the framework boot for publishing;
# none of it is persisted in the image.
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && APP_KEY="base64:$(head -c 32 /dev/urandom | base64)" CACHE_STORE=array DB_CONNECTION=sqlite DB_DATABASE=:memory: \
       sh -c "php artisan package:discover --no-interaction && php artisan vendor:publish --tag=statamic-cp --force --no-interaction" \
    && rm -f .env public/hot \
    && chown -R www-data:www-data storage bootstrap/cache
USER www-data
