#!/bin/sh
set -e

echo "[entrypoint] Starting container as: $(whoami)"
echo "[entrypoint] APP_ENV=${APP_ENV:-local}"

# ── Storage & cache permissions ────────────────────────────────
echo "[entrypoint] Setting storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# ── Wait for PostgreSQL ────────────────────────────────────────
if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" = "pgsql" ]; then
    echo "[entrypoint] Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT:-5432}..."
    until nc -z "$DB_HOST" "${DB_PORT:-5432}"; do
        echo "[entrypoint] PostgreSQL not ready, retrying in 2s..."
        sleep 2
    done
    echo "[entrypoint] PostgreSQL is ready."
fi

# ── Wait for Redis ─────────────────────────────────────────────
if [ -n "$REDIS_HOST" ]; then
    echo "[entrypoint] Waiting for Redis at ${REDIS_HOST}:${REDIS_PORT:-6379}..."
    until nc -z "$REDIS_HOST" "${REDIS_PORT:-6379}"; do
        echo "[entrypoint] Redis not ready, retrying in 2s..."
        sleep 2
    done
    echo "[entrypoint] Redis is ready."
fi

# ── Composer vendor ───────────────────────────────────────────
if [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "[entrypoint] vendor/autoload.php not found, running composer install..."
    composer install --no-interaction --prefer-dist --no-scripts
fi

# ── Laravel bootstrap ──────────────────────────────────────────
echo "[entrypoint] Running artisan optimize:clear..."
php artisan optimize:clear
php artisan cache:clear

echo "[entrypoint] Running migrations..."
php artisan migrate --force --no-interaction

ROLE_COUNT=$(php artisan tinker --execute="echo \App\Models\Role::count();" 2>/dev/null | tail -1)
if [ "$ROLE_COUNT" = "0" ] || [ -z "$ROLE_COUNT" ]; then
    echo "[entrypoint] Running database seeders..."
    php artisan db:seed --force --no-interaction
else
    echo "[entrypoint] Database already seeded, skipping."
fi

if [ "${APP_ENV}" = "production" ]; then
    echo "[entrypoint] Caching config & routes for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

echo "[entrypoint] Bootstrap complete. Executing: $*"
exec "$@"
