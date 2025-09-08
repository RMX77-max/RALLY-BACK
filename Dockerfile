FROM webdevops/php-nginx:8.2

WORKDIR /app

# Extensiones necesarias (Postgres)
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev \
 && docker-php-ext-install pdo pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*

# (Opcional) extensiones útiles si tu app las necesita:
# RUN docker-php-ext-install bcmath intl exif

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia proyecto
COPY . .

# Instala dependencias prod
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Asegura permisos de escritura en storage y cache
RUN chown -R application:application /app \
 && mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache

# Nginx servirá /public
ENV WEB_DOCUMENT_ROOT=/app/public

EXPOSE 8080
