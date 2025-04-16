FROM php:8.2-cli

# Installer quelques dépendances nécessaires
RUN apt-get update && apt-get install -y git unzip curl zip libzip-dev libonig-dev libpng-dev && \
    docker-php-ext-install pdo pdo_mysql zip mbstring

# Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Copier le code
WORKDIR /app
COPY . .

# Installer les dépendances Symfony
RUN composer install

# Exposer le port 8000
EXPOSE 8000

# Lancer le serveur Symfony intégré
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
