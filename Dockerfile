# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# 1. PHP dependencies (cached separately so app-code changes don't bust it)
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ---------------------------------------------------------------------------
# 2. Frontend assets
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources/ resources/
COPY public/ public/

# tailwind.config.js scans these vendor views for class names (Livewire's
# WithPagination trait renders its own bundled pagination view), so they
# need to be present even though the rest of vendor/ isn't.
COPY --from=vendor \
    /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views/ \
    vendor/laravel/framework/src/Illuminate/Pagination/resources/views/
COPY --from=vendor \
    /app/vendor/livewire/livewire/src/Features/SupportPagination/views/ \
    vendor/livewire/livewire/src/Features/SupportPagination/views/

RUN npm run build

# ---------------------------------------------------------------------------
# 3. Runtime image
# ---------------------------------------------------------------------------
FROM serversideup/php:8.5-fpm-nginx-alpine AS production

ENV PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_MIGRATION=true \
    AUTORUN_LARAVEL_STORAGE_LINK=true

COPY --chown=www-data:www-data . /var/www/html
COPY --chown=www-data:www-data --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --chown=www-data:www-data --from=assets /app/public/build/ /var/www/html/public/build/

WORKDIR /var/www/html

# Regenerate the package-discovery cache against the --no-dev vendor set,
# since `composer install --no-scripts` above skipped it.
RUN php artisan package:discover --ansi

USER www-data

EXPOSE 8080
