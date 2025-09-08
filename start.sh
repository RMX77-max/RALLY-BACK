#!/usr/bin/env bash
set -e

# 1) Limpia caches viejos (por si quedaron inconsistencias)
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

# 2) Migra base de datos (si ya está todo OK, no rompe si no hay cambios)
php artisan migrate --force || true

# 3) Crea el symlink de storage (si ya existe, no falla)
php artisan storage:link || true

# 4) Re-cachea con las variables REALES del entorno de Render
php artisan config:cache || true
php artisan route:cache || true

# 5) Arranca Nginx + PHP-FPM (clave para evitar el error de upstream 9000)
exec supervisord
