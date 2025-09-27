FROM php:8.2-apache

# Установка системных зависимостей и composer
RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev libonig-dev \
  && docker-php-ext-install pdo pdo_mysql mysqli zip \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Устанавливаем Composer (копируем из официального образа composer)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Включаем mod_rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

# Копирование composer.json (если есть) и установка php-зависимостей
# (комменты: composer.json должен быть в корне php/ или в проекте; см. инструкции ниже)
COPY composer.json /tmp/composer.json
RUN if [ -f /tmp/composer.json ]; then composer install --no-dev --optimize-autoloader --working-dir=/tmp && mv /tmp/vendor /var/www/html/vendor; fi

# Код проекта будет смонтирован томами в docker-compose
