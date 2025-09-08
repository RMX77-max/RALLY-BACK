FROM webdevops/php-nginx:8.2

WORKDIR /app

# Extensiones y utilidades necesarias
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev \
 && docker-php-ext-install pdo pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*

# (Opcional) instala otras extensiones si tu app las necesita:
# RUN docker-php-ext-install bcmath intl exif gd

# Composer desde la imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia proyecto
COPY . .

# Instala dependencias de producción
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Crea directorios y fija permisos para que Laravel pueda escribir
RUN mkdir -p \
      storage/logs \
      storage/framework/cache \
      storage/framework/sessions \
      storage/framework/views \
      bootstrap/cache \
  && chown -R application:application /app \
  && chmod -R 775 storage bootstrap/cache

# Copia el script de arranque y dale permisos
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Nginx servirá /public
ENV WEB_DOCUMENT_ROOT=/app/public

# Render expone puerto automáticamente; esta imagen usa 8080
EXPOSE 8080

# Arranque: script que hace migraciones, storage:link, cachea y luego lanza supervisord
CMD ["/usr/local/bin/start.sh"]
