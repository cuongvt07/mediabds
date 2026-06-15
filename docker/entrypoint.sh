#!/bin/sh
set -eu

mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/public \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

php artisan package:discover --no-ansi

echo "Waiting for database at ${DB_HOST}:${DB_PORT}..."
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" >/dev/null 2>&1; do
    sleep 2
done

php artisan migrate --force --no-ansi
php artisan storage:link --force --no-ansi || true

if [ "${DOCKER_SEED:-false}" = "true" ]; then
    php artisan db:seed --class=Database\\Seeders\\DockerSeeder --force --no-ansi
fi

exec "$@"
