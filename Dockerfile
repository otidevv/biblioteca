# syntax=docker/dockerfile:1

# ---- Stage 1: build de assets con Vite ----
FROM node:20-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci || npm install
COPY . .
RUN npm run build

# ---- Stage 2: aplicacion PHP ----
FROM php:8.3-fpm-bookworm AS app

# Dependencias del sistema + extensiones PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip default-mysql-client \
        libicu-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mysqli mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Ajustes de PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-custom.ini

WORKDIR /var/www/html

# Codigo fuente de la app
COPY . .

# Assets compilados desde la stage de Node
COPY --from=assets /app/public/build ./public/build

# Dependencias PHP de produccion
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
