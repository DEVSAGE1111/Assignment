FROM php:8.4-apache

# 1. Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring zip xml bcmath

# 2. Enable Apache mod_rewrite and disable conflicting MPM modules
RUN a2enmod rewrite && (a2dismod mpm_event mpm_worker || true)

# 3. Configure Apache DocumentRoot to point to /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Copy project files (this includes public/build assets committed to Git)
WORKDIR /var/www/html
COPY . .

# 5. Install Composer dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 6. Set directory permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# 7. Configure Apache to listen on dynamic port env (default 80 if not set)
ENV PORT=80
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g' /etc/apache2/sites-available/*.conf

# 8. Run migrations, seeders, and start Apache web server
CMD ["sh", "-c", "php artisan migrate --force || echo 'Migrations failed, continuing...'; php artisan db:seed --force || echo 'Seeding failed, continuing...'; apache2-foreground"]
