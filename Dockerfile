FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y libpq-dev

# Install and enable PostgreSQL extensions
RUN docker-php-ext-install pdo pdo_pgsql

# Enable Apache modules
RUN a2enmod rewrite

# Configure PHP
RUN { \
    echo 'display_errors=On'; \
    echo 'error_reporting=E_ALL'; \
    echo 'log_errors=On'; \
} > /usr/local/etc/php/conf.d/error-logging.ini

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create required directories
RUN mkdir -p /var/www/html/uploads \
    && mkdir -p /var/www/html/cache \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/cache

# Configure Apache
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Verify PDO installation
RUN php -r "echo 'Available PDO drivers: ' . implode(', ', PDO::getAvailableDrivers());" \
    && php -r "echo PHP_EOL . 'PDO PostgreSQL extension loaded: ' . (extension_loaded('pdo_pgsql') ? 'Yes' : 'No');"

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
