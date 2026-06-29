FROM php:7.4-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libpng-dev libxml2-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip gd xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Install PHP dependencies (deterministic build)
RUN composer install --no-dev --optimize-autoloader

# ---- PHP CONFIG (MediaWiki tuned) ----
# Increase execution time and memory for heavy MW + Semantic queries
RUN echo "max_execution_time=300" > /usr/local/etc/php/conf.d/mediawiki.ini \
 && echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/mediawiki.ini \
 && echo "upload_max_filesize=50M" >> /usr/local/etc/php/conf.d/mediawiki.ini \
 && echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/mediawiki.ini

# Enable and tune OPcache (important for MW performance)
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache.ini

# ---- APACHE CONFIG ----
# Increase timeout for long requests
RUN echo "Timeout 300" >> /etc/apache2/apache2.conf

# ---- ENTRYPOINT ----
# Copy custom entrypoint for DB wait + update.php
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]

# Expose port (Apache default)
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]