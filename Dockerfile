FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    pkg-config

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

# Install PostgreSQL extensions
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pgsql pdo_pgsql

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy Apache configuration
COPY apache.conf /etc/apache2/sites-available/000-default.conf

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

# Configure PHP to display errors
RUN { \
    echo 'display_errors=On'; \
    echo 'error_reporting=E_ALL'; \
    echo 'log_errors=On'; \
} > /usr/local/etc/php/conf.d/error-logging.ini

# Verify PDO installation
RUN php -r "echo 'PDO drivers available: '; print_r(PDO::getAvailableDrivers());" \
    && php -r "echo '\nPDO enabled: ' . (extension_loaded('pdo') ? 'Yes' : 'No');" \
    && php -r "echo '\nPDO PostgreSQL enabled: ' . (extension_loaded('pdo_pgsql') ? 'Yes' : 'No');"

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
