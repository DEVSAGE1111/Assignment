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

# 2. Enable Apache mod_rewrite and configure prefork MPM
RUN a2enmod rewrite
RUN rm -f /etc/apache2/mods-enabled/mpm_*
RUN a2enmod mpm_prefork

# 3. Configure Apache DocumentRoot to point to /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 4. Copy project files (this includes public/build assets committed to Git)
WORKDIR /var/www/html
COPY . .

# 5. Install Composer dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 6. Set directory permissions for Laravel
RUN touch database/database.sqlite
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# 7. Configure port and key defaults
ENV PORT=8000
ENV APP_KEY=base64:Yqdk4Kt49z6oh6BPQ1WwRKyAuRvsyfs1XKp5HWNVic8=

# 8. Create a clean entrypoint script and set it
RUN cat << 'EOF' > /usr/local/bin/docker-entrypoint.sh
#!/bin/sh
set -e

# Write dynamic port config directly to Apache
echo "Listen $PORT" > /etc/apache2/ports.conf

# Replace VirtualHost port in sites configuration
if [ -f /etc/apache2/sites-available/000-default.conf ]; then
    sed -i "s/<VirtualHost [*]:[0-9]*>/<VirtualHost *:$PORT>/g" /etc/apache2/sites-available/*.conf
    sed -i "s/<VirtualHost [*]:\${PORT}>/<VirtualHost *:$PORT>/g" /etc/apache2/sites-available/*.conf
fi

# Print configurations to stdout for debugging
echo "=== PORTS.CONF ==="
cat /etc/apache2/ports.conf
echo "=== 000-DEFAULT.CONF ==="
cat /etc/apache2/sites-available/000-default.conf

# Run database migrations and seeding
php artisan migrate --force || echo "Migrations failed, continuing..."
php artisan db:seed --force || echo "Seeding failed, continuing..."

# Correct directory permissions for Laravel
chown -R www-data:www-data /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache || true

# Configure MPM
a2dismod mpm_event mpm_worker || true
a2enmod mpm_prefork || true

# Run Apache foreground
exec apache2-foreground
EOF

RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]


