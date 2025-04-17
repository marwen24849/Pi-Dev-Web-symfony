FROM php:8.2-fpm-alpine

# Installe les extensions PHP nécessaires
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer



