# Utiliser l'image PHP officielle avec Apache
FROM php:8.1-apache

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install opcache

# Activer mod_rewrite pour .htaccess
RUN a2enmod rewrite

# Copier les fichiers du projet
COPY . /var/www/html/

# Créer les dossiers nécessaires
RUN mkdir -p /var/www/html/plugins /var/www/html/logs && \
    chmod 777 /var/www/html/logs

# Configurer Apache pour accepter .htaccess
RUN echo '<Directory /var/www/html/>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/override.conf && \
    a2enconf override

# Exposer le port 80
EXPOSE 80

# Démarrer Apache
CMD ["apache2-foreground"]
