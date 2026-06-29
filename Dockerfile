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
# COPY APPLICATION
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
# CLONE EXTENSIONS USING LATEST STABLE TAG
# --------------------------------------------------
RUN rm -rf extensions \
 && mkdir -p extensions \
 && cd extensions \
 \
 && fetch_repo() { \
    REPO=$1; \
    NAME=$(basename $REPO .git); \
    echo "Cloning $NAME..."; \
    git clone --quiet --depth 1 $REPO; \
    cd $NAME; \
    TAG=$(git tag --sort=-v:refname | head -n 1); \
    if [ -n "$TAG" ]; then \
        echo "Checking out tag $TAG"; \
        git checkout $TAG; \
    fi; \
    cd ..; \
 }; \
 \
 && fetch_repo https://github.com/SemanticMediaWiki/SemanticMediaWiki.git \
 && fetch_repo https://github.com/SemanticMediaWiki/SemanticResultFormats.git \
 && fetch_repo https://github.com/SemanticMediaWiki/SemanticCompoundQueries.git \
 && fetch_repo https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties.git \
 && fetch_repo https://github.com/SemanticMediaWiki/SemanticMetaTags.git \
 && fetch_repo https://github.com/SemanticMediaWiki/SemanticGlossary.git \
 && fetch_repo https://github.com/SemanticMediaWiki/SemanticDrilldown.git \
 && fetch_repo https://github.com/SemanticMediaWiki/SemanticCite.git \
 && fetch_repo https://github.com/SemanticMediaWiki/SemanticDependencyUpdater.git \
 && fetch_repo https://github.com/wikimedia/mediawiki-extensions-PageForms.git \
 && git clone --depth 1 https://github.com/ProfessionalWiki/Maps.git Maps \
 && fetch_repo https://github.com/ProfessionalWiki/ModernTimeline.git \
 && fetch_repo https://github.com/wikimedia/mediawiki-extensions-Widgets.git

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
    else \
      echo "Maps not found, skipping"; \
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