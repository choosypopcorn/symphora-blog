# Use the official PHP 8.5 FPM image
FROM php:8.5-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql zip

# Set working directory
WORKDIR /var/www/html
# Copy composer files first
COPY composer.json composer.lock ./

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install dependencies without running auto-scripts
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

# Copy all application files including bin/console
COPY . .

# Make sure bin/console has execute permissions
# RUN chmod +x bin/console

# Set permissions
RUN mkdir -p var/cache var/logs var/sessions
RUN chmod 775 -R var/
RUN chown -R www-data:www-data var/

# Expose port 9000 (PHP-FPM default)
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]