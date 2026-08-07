#!/bin/sh
set -e

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    mkdir -p database
    touch database/database.sqlite
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache
if [ "${RUN_SEEDER:-false}" = "true" ]; then
    php artisan migrate:fresh --seed --force
else
    php artisan migrate --force
fi

php artisan storage:link 2>/dev/null || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
