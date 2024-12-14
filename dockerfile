FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    iputils-ping \
    curl \
    git \
    nodejs \
    npm \
    && docker-php-ext-install pdo_pgsql zip

# Instalar Yarn (si decides usar Yarn)
RUN npm install -g yarn

# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Configuración del directorio de trabajo
WORKDIR /var/www

# Copiar todos los archivos del proyecto
COPY . .

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# Instalar dependencias de Node usando Yarn
RUN yarn install

# Construir los assets de frontend usando Yarn
RUN yarn build

# Ejecutar migraciones y optimizar la aplicación
RUN php artisan migrate --force && php artisan optimize

# Ajustar permisos
RUN chmod -R 775 storage bootstrap/cache && php artisan storage:link

# Exponer el puerto de PHP-FPM
EXPOSE 9000

# Iniciar PHP-FPM
CMD ["php-fpm"]
