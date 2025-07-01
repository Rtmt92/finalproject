FROM php:8.2-apache

WORKDIR /var/www/html

# copie tout le code
COPY . .

# installe PDO + MySQL
RUN docker-php-ext-install pdo pdo_mysql

# active mod_rewrite si besoin
RUN a2enmod rewrite

# s’assure que les logs s’affichent
ENV APACHE_LOG_DIR /var/log/apache2

# démarrage par défaut
CMD ["apache2-foreground"]
