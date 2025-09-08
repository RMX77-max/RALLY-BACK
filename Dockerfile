# Imagen con PHP + Nginx ya listos (8.2)
FROM webdevops/php-nginx:8.2

# Directorio de trabajo
WORKDIR /app

# Instala extensiones necesarias para Postgres
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev \
 && docker-php-ext-install pdo pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*

# Copia composer desde la imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia todos los archivos del proyecto al contenedor
COPY . .

# Instala dependencias (modo producción)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Optimiza cache de configuración y rutas de Laravel
RUN php artisan config:cache && php artisan route:cache

# Nginx servirá la carpeta /public
ENV WEB_DOCUMENT_ROOT=/app/public

# Render expone el puerto automáticamente
EXPOSE 8080
