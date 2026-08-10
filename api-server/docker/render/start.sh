#!/bin/sh
set -e

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    mkdir -p database
    touch database/database.sqlite
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "${DB_FRESH:-false}" = "true" ]; then
    if [ "${RUN_SEEDER:-false}" = "true" ]; then
        php artisan migrate:fresh --seed --force
    else
        php artisan migrate:fresh --force
    fi
else
    php artisan migrate --force

    if [ "${RUN_SEEDER:-false}" = "true" ]; then
        php artisan db:seed --force
    fi
fi

php artisan storage:link 2>/dev/null || true

PORT="${PORT:-10000}"
export PORT
envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

php-fpm -D

if [ "${QUEUE_CONNECTION:-sync}" != "sync" ]; then
    php artisan queue:work --sleep=3 --tries=3 --timeout=600 &
fi

exec nginx -g 'daemon off;'
