FROM php:8.3-cli

# 1) Dependencias del sistema (PostgreSQL + build tools para pecl/redis)
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev \
    autoconf build-essential pkg-config libssl-dev \
 && docker-php-ext-install pdo pdo_pgsql

# 2) Instalar extensión Redis (phpredis) vía PECL y habilitarla
RUN pecl install redis \
 && docker-php-ext-enable redis

# 3) Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4) App
WORKDIR /var/www/html
COPY . .

# 5) Dependencias de Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader || true

# 6) Clave de app
RUN php artisan key:generate || true
