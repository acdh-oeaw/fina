FROM php:8.1-apache

# --------------------------------------------------
# SYSTEM DEPENDENCIES
# --------------------------------------------------
RUN apt-get update && apt-get install -y \
    git unzip \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libonig-dev \
    curl \
 && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# PHP EXTENSIONS
# --------------------------------------------------
RUN docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip \
    gd \
    xml \
    calendar

# Apache config
RUN a2enmod rewrite \
 && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# --------------------------------------------------
# COMPOSER
# --------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# --------------------------------------------------
# COPY APP
# --------------------------------------------------
COPY . .

# --------------------------------------------------
# INSTALL CORE DEPENDENCIES
# --------------------------------------------------
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --ignore-platform-reqs

# --------------------------------------------------
# CLONE MEDIAWIKI EXTENSIONS
# --------------------------------------------------
RUN mkdir -p extensions && cd extensions && \
# --- Semantic core ---
git clone https://github.com/SemanticMediaWiki/SemanticMediaWiki.git && \
git clone https://github.com/SemanticMediaWiki/SemanticResultFormats.git && \

# --- SMW ecosystem ---
git clone https://github.com/SemanticMediaWiki/SemanticCompoundQueries.git && \
git clone https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties.git && \
git clone https://github.com/SemanticMediaWiki/SemanticMetaTags.git && \
git clone https://github.com/SemanticMediaWiki/SemanticGlossary.git && \
git clone https://github.com/SemanticMediaWiki/SemanticDrilldown.git && \
git clone https://github.com/SemanticMediaWiki/SemanticCite.git && \
git clone https://github.com/SemanticMediaWiki/SemanticDependencyUpdater.git && \
git clone https://github.com/wikimedia/mediawiki-extensions-PageForms.git PageForms && \

# --- external ---
git clone https://github.com/ProfessionalWiki/Maps.git && \
git clone https://github.com/ProfessionalWiki/ModernTimeline.git && \
git clone https://github.com/wikimedia/mediawiki-extensions-Widgets.git Widgets

# --------------------------------------------------
# INSTALL EXTENSION COMPOSER DEPENDENCIES
# --------------------------------------------------
RUN set -ex \
 && cd extensions/SemanticMediaWiki && composer install --no-dev --no-interaction \
 && cd ../SemanticResultFormats && composer install --no-dev --no-interaction \
 && cd ../Maps && composer install --no-dev --no-interaction || true \
 && cd /var/www/html

# --------------------------------------------------
# PERMISSIONS
# --------------------------------------------------
RUN chown -R www-data:www-data /var/www/html

# --------------------------------------------------
# PHP CONFIG
# --------------------------------------------------
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/media.ini \
 && echo "max_execution_time=360" >> /usr/local/etc/php/conf.d/media.ini \
 && echo "max_input_time=360" >> /usr/local/etc/php/conf.d/media.ini \
 && echo "upload_max_filesize=256M" >> /usr/local/etc/php/conf.d/media.ini \
 && echo "post_max_size=257M" >> /usr/local/etc/php/conf.d/media.ini

# OPcache
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache.ini

# Apache timeout
RUN echo "Timeout 300" >> /etc/apache2/apache2.conf

# --------------------------------------------------
# ENTRYPOINT
# --------------------------------------------------
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]

EXPOSE 80
CMD ["apache2-foreground"]