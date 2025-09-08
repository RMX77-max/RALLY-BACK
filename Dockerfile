FROM webdevops/php-nginx:8.2

WORKDIR /app

# Extensiones necesarias (Postgres) y utilidades
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev \
 && docker-php-ext-install pdo pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*

# (Opcional) instala otras extensiones si tu app las necesita:
# RUN docker-php-ext-install bcmath intl exif gd

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia el proyecto
COPY . .

# Instala dependencias de producción
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Crea y fija permisos en directorios que Laravel necesita escribir
RUN mkdir -p \
      storage/logs \
      storage/framework/cache \
      storage/framework/sessions \
      storage/framework/views \
      bootstrap/cache \
  && chown -R application:application /app \
  && chmod -R 775 storage bootstrap/cache

# Nginx servirá /public
ENV WEB_DOCUMENT_ROOT=/app/public

# Asegura que arranque Nginx + PHP-FPM vía supervisord
CMD ["supervisord"]

EXPOSE 8080
