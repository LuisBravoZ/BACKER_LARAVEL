FROM php:8.2-fpm

# Instalar dependencias del sistema necesarias para Laravel y Node.js
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    iputils-ping \
    curl \
    nodejs \
    npm \
    && docker-php-ext-install pdo_pgsql zip

# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Configuración del directorio de trabajo
WORKDIR /var/www

# Copiar los archivos del proyecto
COPY . .

# Instalar las dependencias de Composer (PHP)
RUN echo "Instalando dependencias de Composer" && composer install --no-dev --optimize-autoloader

# Instalar dependencias de NPM y construir assets
RUN echo "Instalando dependencias de NPM" && npm install && echo "Dependencias de NPM instaladas"
RUN echo "Construyendo assets con npm" && npm run build && echo "Assets construidos"

# Ejecutar migraciones de la base de datos
RUN echo "Ejecutando migraciones" && php artisan migrate --force && echo "Migraciones completadas"

# Optimizar el proyecto
RUN echo "Optimizando el proyecto" && php artisan optimize && echo "Optimización completada"

# Ajustar permisos de los directorios
RUN echo "Ajustando permisos" && chmod 777 -R storage/ && chmod 777 -R bootstrap/cache

# Crear el enlace simbólico para el almacenamiento
RUN echo "Creando enlace simbólico de storage" && php artisan storage:link && echo "Enlace simbólico creado"

# Exponer el puerto de PHP-FPM
EXPOSE 9000

# Iniciar PHP-FPM
CMD ["php-fpm"]
