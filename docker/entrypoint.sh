#!/bin/sh
set -eu

cd /var/www/html

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

KEY_FILE=storage/app/.docker_app_key
if [ -z "${APP_KEY:-}" ]; then
    if [ -f "$KEY_FILE" ]; then
        APP_KEY="$(cat "$KEY_FILE")"
    else
        APP_KEY="base64:$(head -c 32 /dev/urandom | base64 | tr -d '\n')"
        printf '%s' "$APP_KEY" > "$KEY_FILE"
    fi
    export APP_KEY
fi

echo "Attente de MySQL (${DB_HOST:-mysql}:${DB_PORT:-3306})..."
i=0
until php -r '
    $h = getenv("DB_HOST") ?: "mysql";
    $p = getenv("DB_PORT") ?: "3306";
    $d = getenv("DB_DATABASE") ?: "gestion_credit";
    $u = getenv("DB_USERNAME") ?: "gestion";
    $w = getenv("DB_PASSWORD") ?: "";
    new PDO("mysql:host=$h;port=$p;dbname=$d", $u, $w);
' >/dev/null 2>&1; do
    i=$((i + 1))
    if [ "$i" -ge 60 ]; then
        echo "MySQL est inaccessible."
        exit 1
    fi
    sleep 2
done

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link --force >/dev/null 2>&1 || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache

echo "Application prête."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
