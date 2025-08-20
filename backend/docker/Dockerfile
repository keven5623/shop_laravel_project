# 使用官方 PHP FPM 映像
FROM php:8.2-fpm

# 安裝系統依賴
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libonig-dev libzip-dev zip curl \
    && docker-php-ext-install pdo pdo_mysql mbstring zip

# 安裝 Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 設定工作目錄
WORKDIR /var/www/html

# 複製 Laravel 專案
COPY ../ ./

# 安裝 PHP 套件
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# 設定 storage 權限
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Expose FPM port
EXPOSE 9000

CMD ["php-fpm"]
