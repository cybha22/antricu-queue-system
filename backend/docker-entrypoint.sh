#!/bin/bash
set -e

echo "=== Antricu Backend Entrypoint ==="

if [ ! -f .env ]; then
    echo "[1/5] Menyalin .env.example ke .env..."
    cp .env.example .env
fi

echo "[2/5] Generating application key..."
php artisan key:generate --force --no-interaction

echo "[3/5] Menunggu database siap..."
MAX_RETRIES=30
RETRY_COUNT=0
until php artisan db:monitor --databases=mysql > /dev/null 2>&1 || mysqladmin ping -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent 2>/dev/null; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
        echo "Database tidak dapat dihubungi setelah ${MAX_RETRIES} percobaan."
        break
    fi
    echo "Menunggu database... (${RETRY_COUNT}/${MAX_RETRIES})"
    sleep 2
done

echo "[4/5] Menjalankan migrasi database..."
php artisan migrate --force --no-interaction

echo "[5/5] Menjalankan seeder (jika tabel kosong)..."
php artisan db:seed --force --no-interaction 2>/dev/null || echo "Seeder sudah pernah dijalankan atau data sudah ada."

echo "=== Backend siap! ==="

exec "$@"
