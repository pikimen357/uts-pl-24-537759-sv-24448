FROM php:8.4-cli-alpine

# Composer dengan versi yang jelas
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Ekstensi PHP untuk MySQL
RUN docker-php-ext-install pdo_mysql

WORKDIR /app

# 1) Salin hanya composer.json dan composer.lock, lalu pasang dependensi.
#    Lapisan ini di-cache selama kedua file itu tidak berubah.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

# 2) Baru salin kode aplikasi
COPY . .

# 3) Autoloader, folder yang dibutuhkan Laravel, dan izin tulis
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/logs \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
