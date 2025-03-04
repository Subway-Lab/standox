# Используем официальный PHP-образ с Apache
FROM php:7.4-apache

# Включаем поддержку MySQL, если нужно
RUN docker-php-ext-install mysqli

# Копируем все файлы проекта в контейнер
COPY . /var/www/html/

# Открываем порт 80, на котором будет работать Apache
EXPOSE 80