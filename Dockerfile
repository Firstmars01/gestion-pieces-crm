FROM php:8.3-apache

# 1. Installation des dépendances (ajout de libpng/libjpeg pour l'extension GD nécessaire à Dompdf)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql zip gd

# 2. Activation du module de réécriture Apache (indispensable pour Symfony)
RUN a2enmod rewrite

# 3. Modification du DocumentRoot d'Apache pour pointer vers /public et activation du .htaccess
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Autoriser Apache à lire le fichier .htaccess de Symfony
RUN printf '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' >> /etc/apache2/apache2.conf

# 4. Récupération de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 5. Copie des fichiers du projet
COPY . .

# 6. Variables d'environnement pour le build de Symfony (évite d'avoir besoin de la bdd au build)
ENV APP_ENV=prod

# 7. Installation des dépendances SANS LIMITE DE MÉMOIRE et via GIT
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-scripts --prefer-source

# 8. Gestion des permissions pour Symfony (le cache et les logs)
RUN mkdir -p var && chown -R www-data:www-data var

EXPOSE 80

# On FORCE la mise à jour du schéma de la BDD, puis on lance les fixtures
CMD php bin/console doctrine:schema:update --force --env=prod \
    && php bin/console doctrine:fixtures:load --no-interaction --env=prod \
    && php bin/create_admin.php \
    && chown -R www-data:www-data var \
    && apache2-foreground
