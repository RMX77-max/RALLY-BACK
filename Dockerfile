FROM webdevops/php-nginx:8.3

WORKDIR /app

# Ajustes PHP útiles (webdevops soporta estas envs)
ENV PHP_DATE_TIMEZONE=UTC \
    PHP_DISPLAY_ERRORS=0 \
    PHP_MEMORY_LIMIT=256M \
    PHP_UPLOAD_MAX_FILESIZE=20M \
    PHP_POST_MAX_SIZE=20M \
    WEB_DOCUMENT_ROOT=/app/public

# Extensiones y utilidades necesarias
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev \
 && docker-php-ext-install pdo pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*

# Composer desde la imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- capa de dependencias para cachear composer ---
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copia el resto del proyecto
COPY . .

# Vuelve a optimizar autoload por si hay clases nuevas
RUN composer dump-autoload -o

# Directorios y permisos para Laravel
RUN mkdir -p \
      storage/logs \
      storage/framework/cache \
      storage/framework/sessions \
      storage/framework/views \
      bootstrap/cache \
  && chown -R application:application /app \
  && chmod -R 775 storage bootstrap/cache

# Script de arranque
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]
