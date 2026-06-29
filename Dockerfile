FROM php:7.4-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libpng-dev libxml2-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip gd xml calendar \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache modules (required for MediaWiki + .htaccess)
RUN a2enmod rewrite

# Allow .htaccess overrides (CRITICAL for your rewrite rules!)
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy app
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# ---- PHP CONFIG (your settings) ----
RUN echo "max_execution_time=360" > /usr/local/etc/php/conf.d/mediawiki.ini \
 && echo "max_input_time=360" >> /usr/local/etc/php/conf.d/mediawiki.ini \
 && echo "upload_max_filesize=256M" >> /usr/local/etc/php/conf.d/mediawiki.ini \
 && echo "post_max_size=257M" >> /usr/local/etc/php/conf.d/mediawiki.ini \
 && echo "memory_limit=258M" >> /usr/local/etc/php/conf.d/mediawiki.ini

# ---- OPcache (recommended for MW) ----
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache.ini

# ---- APACHE TIMEOUT ----
RUN echo "Timeout 300" >> /etc/apache2/apache2.conf

# ---- ENTRYPOINT ----
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]

EXPOSE 80

CMD ["apache2-foreground"]