FROM php:8.3-apache

# Extensiones necesarias: pdo_pgsql, redis
RUN apt-get update && apt-get install -y libpq-dev git unzip \
 && docker-php-ext-install pdo pdo_pgsql \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && a2enmod rewrite

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.json ./
RUN composer install --no-dev --prefer-dist --no-interaction

COPY public/ public/
COPY src/ src/

# Apache DocumentRoot
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80
CMD ["apache2-foreground"]
