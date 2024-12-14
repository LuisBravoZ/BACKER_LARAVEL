# Usar una imagen base de PHP con soporte para Composer y Node
FROM php:8.1-fpm

# Instalar dependencias del sistema para Composer y Node
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip git curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql

# Instalar Node.js
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash - && \
    apt-get install -y nodejs

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar el código fuente a la imagen del contenedor
COPY . /app/

# Establecer el directorio de trabajo
WORKDIR /app

# Instalar las dependencias de PHP con Composer
RUN echo "Installing Composer dependencies..." && composer install --no-dev --optimize-autoloader

# Instalar dependencias de Node.js y construir el proyecto
RUN echo "Installing Node dependencies..." && npm install && npm run build

# Migrar la base de datos (asegúrate de que la base de datos sea accesible desde el contenedor)
RUN echo "Running migrations..." && php artisan migrate --force

# Optimizar el proyecto (por ejemplo, cache de rutas, configuración, etc.)
RUN echo "Optimizing application..." && php artisan optimize

# Establecer permisos adecuados para los directorios de Laravel
RUN echo "Setting permissions..." && chmod -R 777 storage/ bootstrap/cache/

# Crear el enlace simbólico para almacenamiento
RUN echo "Creating storage symlink..." && php artisan storage:link

# Exponer el puerto 9000 para el contenedor
EXPOSE 9000

# Iniciar PHP-FPM
CMD ["php-fpm"]
