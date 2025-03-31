FROM php:8.4-fpm

RUN apt-get update && apt-get install -y --no-install-recommends git curl zip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    libpq-dev \
    libc-client-dev \
    libkrb5-dev \
    nano

RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql zip exif

RUN docker-php-source delete

# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Копируем файлы Composer перед установкой зависимостей
COPY ./backend /app

# Устанавливаем рабочую директорию
WORKDIR /app

# Устанавливаем зависимости без dev-пакетов и с учетом кеша
RUN composer install --no-dev --prefer-dist --no-scripts --no-autoloader

# Запускаем сборку автозагрузки
RUN composer dump-autoload --optimize

# Открываем порт для PHP-FPM
EXPOSE 9000

# Запускаем PHP-FPM
CMD ["php-fpm"]
