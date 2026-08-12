#!/bin/sh
set -e

prepare_runtime_permissions() {
    mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
        database

    chown -R www-data:www-data storage bootstrap/cache database
    chmod -R ug+rwx storage bootstrap/cache database
}

wait_for_mysql() {
    if [ "${DB_CONNECTION:-}" != "mysql" ]; then
        return 0
    fi

    host="${DB_HOST:-127.0.0.1}"
    port="${DB_PORT:-3306}"
    attempts="${DB_WAIT_ATTEMPTS:-30}"
    delay="${DB_WAIT_DELAY:-2}"

    echo "Waiting for MySQL at ${host}:${port}..."

    attempt=0
    while [ "$attempt" -lt "$attempts" ]; do
        if php -r '
            try {
                new PDO(
                    sprintf(
                        "mysql:host=%s;port=%s;dbname=%s",
                        getenv("DB_HOST") ?: "127.0.0.1",
                        getenv("DB_PORT") ?: "3306",
                        getenv("DB_DATABASE") ?: ""
                    ),
                    getenv("DB_USERNAME") ?: "",
                    getenv("DB_PASSWORD") ?: "",
                    [PDO::ATTR_TIMEOUT => 2]
                );
                exit(0);
            } catch (Throwable $e) {
                exit(1);
            }
        '; then
            echo "MySQL is ready."
            return 0
        fi

        attempt=$((attempt + 1))
        sleep "$delay"
    done

    echo "MySQL did not become ready in time." >&2
    return 1
}

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    mkdir -p database
    touch database/database.sqlite
fi

wait_for_mysql

prepare_runtime_permissions

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

if [ "${FILESYSTEM_PUBLIC_DISK:-public}" = "public" ]; then
    php artisan storage:link 2>/dev/null || true
fi

prepare_runtime_permissions

export TMPDIR="/var/www/html/storage/framework/cache/data"

PORT="${PORT:-80}"
export PORT
envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

php-fpm -D

if [ "${QUEUE_CONNECTION:-sync}" != "sync" ]; then
    php artisan queue:work --sleep=3 --tries=3 --timeout=600 &
fi

exec nginx -g 'daemon off;'
