FROM php:8.2-apache

# Устанавливаем расширение PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Включаем mod_rewrite (если планируется ЧПУ)
RUN a2enmod rewrite
