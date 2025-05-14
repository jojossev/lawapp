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
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo && \
    docker-php-ext-configure pgsql && \
    docker-php-ext-install pgsql && \
    docker-php-ext-install pdo_pgsql && \
    docker-php-ext-install mbstring exif pcntl bcmath gd

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

# Configure PHP
RUN { \
    echo 'extension=pdo.so'; \
    echo 'extension=pdo_pgsql.so'; \
    echo 'extension=pgsql.so'; \
} > /usr/local/etc/php/conf.d/pgsql.ini

# Display PHP info for debugging
RUN php -m | grep -i pdo

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
