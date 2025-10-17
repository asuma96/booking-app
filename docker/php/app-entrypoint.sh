#!/usr/bin/env bash
set -eo pipefail
cd /var/www/html

[ -f .env ] || cp .env.example .env || true

if [ -f composer.json ] && [ ! -d vendor ]; then
  export COMPOSER_ALLOW_SUPERUSER=1
  composer install --no-interaction --prefer-dist
fi

php artisan key:generate --force || true

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rw storage bootstrap/cache || true

host="${DB_HOST:-mysql}"; port="${DB_PORT:-3306}"
echo "Waiting for DB ${host}:${port}..."
until (</dev/tcp/$host/$port) &>/dev/null; do sleep 2; done
echo "DB is up."

php artisan migrate --force --seed || true
php artisan storage:link || true

exec "$@"
