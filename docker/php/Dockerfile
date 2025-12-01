FROM php:8.1-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установка рабочей директории
WORKDIR /var/www/yii2-advanced

# Копирование конфигурации PHP
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini \
    && echo "upload_max_filesize = 128M" >> /usr/local/etc/php/php.ini \
    && echo "post_max_size = 128M" >> /usr/local/etc/php/php.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/php.ini

# Настройка прав
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Не переключаемся на www-data для Windows
# USER www-data

EXPOSE 9000

CMD ["php-fpm"]