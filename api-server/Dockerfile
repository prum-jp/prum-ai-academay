FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.ts tsconfig.json tsconfig.app.json tsconfig.node.json ./
COPY resources ./resources
COPY public ./public

RUN npm run build

FROM php:8.4-fpm-bookworm AS app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        nginx \
        gettext-base \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libsqlite3-dev \
        default-libmysqlclient-dev \
    && docker-php-ext-install \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        zip \
        bcmath \
        gd \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /etc/nginx/sites-enabled/default

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --no-interaction

COPY . .

COPY --from=frontend /app/public/build ./public/build

COPY docker/render/nginx.conf /etc/nginx/conf.d/default.conf.template

RUN composer dump-autoload --optimize \
    && chmod +x docker/render/start.sh

ENV PORT=10000

EXPOSE 10000

CMD ["docker/render/start.sh"]
