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
RUN git config --global --unset credential.helper || true \
 && git config --global credential.helper "" \
 && git config --global url."https://github.com/".insteadOf "git@github.com:" \
 \
 && export GIT_TERMINAL_PROMPT=0 \
 \
 && rm -rf extensions \
 && mkdir -p extensions \
 && cd extensions \
 \
 && echo "=== SMW core ===" \
 && git clone --depth=1 --branch 4.2.0 https://github.com/SemanticMediaWiki/SemanticMediaWiki.git \
 && git clone --depth=1 --branch 5.2.0 https://github.com/SemanticMediaWiki/SemanticResultFormats.git \
 \
 && echo "=== SMW ecosystem ===" \
 && git clone --depth=1 --branch 4.0.1 https://github.com/SemanticMediaWiki/SemanticCompoundQueries.git \
 && git clone --depth=1 --branch v0.2.6 https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties.git \
 && git clone --depth=1 --branch 5.0.0 https://github.com/SemanticMediaWiki/SemanticMetaTags.git \
 && git clone --depth=1 --branch 7.0.0 https://github.com/SemanticMediaWiki/SemanticGlossary.git \
 && git clone --depth=1 --branch 5.0.2 https://github.com/SemanticMediaWiki/SemanticDrilldown.git \
 && git clone --depth=1 --branch 5.0.0 https://github.com/SemanticMediaWiki/SemanticCite.git \
 \
 && echo "=== Forms ===" \
 && git clone --depth=1 --branch REL1_42 https://github.com/wikimedia/mediawiki-extensions-PageForms.git PageForms \
 \
&& echo "=== External ===" \
&& mkdir Validator \
&& curl -L https://codeload.github.com/wikimedia/mediawiki-extensions-Validator/tar.gz/refs/heads/master \
 | tar -xz --strip-components=1 -C Validator \
 \
&& mkdir ParamProcessor \
&& curl -L https://codeload.github.com/wikimedia/mediawiki-extensions-ParamProcessor/tar.gz/refs/heads/master \
 | tar -xz --strip-components=1 -C ParamProcessor \


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