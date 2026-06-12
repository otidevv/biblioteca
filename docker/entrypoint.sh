#!/usr/bin/env bash
set -e

DB_HOST="${DB_HOST:-mysql}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-biblioteca_unamad}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"

mysql_cmd() {
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$@"
}

echo "[entrypoint] Esperando a MySQL en ${DB_HOST}:${DB_PORT}..."
until mysql_cmd -e "SELECT 1" >/dev/null 2>&1; do
    sleep 2
done
echo "[entrypoint] MySQL disponible."

if [ "${RUN_SETUP:-0}" = "1" ]; then
    echo "[entrypoint] Limpiando caches de configuracion..."
    php artisan optimize:clear || true

    echo "[entrypoint] Ejecutando migraciones..."
    php artisan migrate --force

    # Los seeders contienen inserts NO idempotentes (idiomas, dewey, etc.):
    # solo se ejecutan en una base recien creada.
    SEED_COUNT="$(mysql_cmd -N "$DB_DATABASE" -e "SELECT COUNT(*) FROM idiomas" 2>/dev/null || echo 0)"
    if [ "${SEED_COUNT:-0}" = "0" ]; then
        echo "[entrypoint] Sembrando datos iniciales..."
        php artisan db:seed --force || echo "[entrypoint] AVISO: los seeders fallaron, continuando."
    else
        echo "[entrypoint] Datos ya sembrados (idiomas=${SEED_COUNT}); se omite el seed."
    fi

    php artisan storage:link 2>/dev/null || true
else
    echo "[entrypoint] Esperando a que las migraciones esten aplicadas..."
    until mysql_cmd -N "$DB_DATABASE" -e "SELECT 1 FROM users LIMIT 1" >/dev/null 2>&1; do
        sleep 3
    done
    echo "[entrypoint] Migraciones detectadas."
fi

echo "[entrypoint] Iniciando proceso: $*"
exec "$@"
