FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

WORKDIR /var/www/html

COPY . /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY sqlScripts.sql /var/www/html/sqlScripts.sql

RUN mkdir -p /var/www/html/Database \
    && chown -R www-data:www-data /var/www/html/Database

RUN sqlite3 /var/www/html/Database/main.sqlite < /var/www/html/sqlScripts.sql

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

RUN a2enmod rewrite

EXPOSE 80

CMD ["apache2-foreground"]
