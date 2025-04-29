# POO/Dockerfile
FROM php:8.3-apache

# Installer l’extension PDO_MySQL
RUN docker-php-ext-install pdo_mysql


RUN { \
    echo 'display_errors=On'; \
    echo 'display_startup_errors=On'; \
} > /usr/local/etc/php/conf.d/docker-php-errors.ini