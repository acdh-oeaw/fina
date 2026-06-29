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

# --------------------------------------------------
# APACHE CONFIG
# --------------------------------------------------
RUN a2enmod rewrite \
 && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
 && echo "Timeout 300" >> /etc/apache2/apache2.conf

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
# INSTALL ROOT DEPENDENCIES
# --------------------------------------------------
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --ignore-platform-reqs

# --------------------------------------------------
# CLONE EXTENSIONS (PINNED VERSIONS)
# --------------------------------------------------
RUN rm -rf extensions \
 && mkdir -p extensions \
 && cd extensions \

# --- SMW core ---
 && git clone https://github.com/SemanticMediaWiki/SemanticMediaWiki.git \
 && cd SemanticMediaWiki && git checkout 4.2.0 && cd .. \

 && git clone https://github.com/SemanticMediaWiki/SemanticResultFormats.git \
 && cd SemanticResultFormats && git checkout 5.2.0 && cd .. \

# --- SMW ecosystem ---
 && git clone https://github.com/SemanticMediaWiki/SemanticCompoundQueries.git \
 && cd SemanticCompoundQueries && git checkout 4.0.1 && cd .. \

 && git clone https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties.git \
 && cd SemanticExtraSpecialProperties && git checkout v0.2.6 && cd .. \

 && git clone https://github.com/SemanticMediaWiki/SemanticMetaTags.git \
 && cd SemanticMetaTags && git checkout 5.0.0 && cd .. \

 && git clone https://github.com/SemanticMediaWiki/SemanticGlossary.git \
 && cd SemanticGlossary && git checkout 7.0.0 && cd .. \

 && git clone https://github.com/SemanticMediaWiki/SemanticDrilldown.git \
 && cd SemanticDrilldown && git checkout 5.0.2 && cd .. \

 && git clone https://github.com/SemanticMediaWiki/SemanticCite.git \
 && cd SemanticCite && git checkout 5.0.0 && cd .. \

# --- forms ---
 && git clone https://github.com/wikimedia/mediawiki-extensions-PageForms.git PageForms \
 && cd PageForms && git checkout REL1_42 && cd .. \

# --- external ---
 && git clone https://github.com/wikimedia/mediawiki-extensions-Validator.git Validator \
 && git clone https://github.com/wikimedia/mediawiki-extensions-ParamProcessor.git ParamProcessor \
 && git clone https://github.com/ProfessionalWiki/Maps.git \
 && git clone https://github.com/ProfessionalWiki/ModernTimeline.git \
 && cd ModernTimeline && git checkout 4.0.0 && cd .. \

 && git clone https://github.com/wikimedia/mediawiki-extensions-Widgets.git Widgets \
 && cd Widgets && git checkout REL1_42 && cd ..

# --------------------------------------------------
# INSTALL EXTENSION DEPENDENCIES
# --------------------------------------------------
RUN set -ex \
 && cd /var/www/html/extensions \
 \
 && cd SemanticMediaWiki && composer install --no-dev --no-interaction && cd .. \
 && cd SemanticResultFormats && composer install --no-dev --no-interaction && cd .. \
 \
 && if [ -d "Maps" ]; then \
      cd Maps && composer install --no-dev --no-interaction && cd .. ; \
    fi \
 \
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

# --------------------------------------------------
# OPCACHE
# --------------------------------------------------
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache.ini

# --------------------------------------------------
# ENTRYPOINT
# --------------------------------------------------
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]

EXPOSE 80
CMD ["apache2-foreground"]