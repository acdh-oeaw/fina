FROM php:8.1-apache

# --------------------------------------------------
# SYSTEM DEPENDENCIES
# --------------------------------------------------
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    lua5.1 \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libonig-dev \
 && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# PHP EXTENSIONS
# --------------------------------------------------
RUN docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    mysqli \
    zip \
    gd \
    xml \
    calendar


# --------------------------------------------------
# APACHE
# --------------------------------------------------
RUN a2enmod rewrite \
 && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# --------------------------------------------------
# COMPOSER
# --------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# --------------------------------------------------
# APPLICATION
# --------------------------------------------------
COPY . .

ENV GIT_TERMINAL_PROMPT=0
# --------------------------------------------------
# EXTENSIONS
# --------------------------------------------------
RUN mkdir -p /var/www/html/extensions \
 && cd /var/www/html/extensions \
 \
 && echo "=== Core MW extensions ===" \
 && git clone --depth=1 --branch REL1_39 https://github.com/wikimedia/mediawiki-extensions-ParserFunctions.git ParserFunctions \
 && git clone --depth=1 --branch REL1_39 https://github.com/wikimedia/mediawiki-extensions-Scribunto.git Scribunto \
 && git clone --depth=1 --branch REL1_39 https://github.com/wikimedia/mediawiki-extensions-Cite.git Cite \
 && git clone --depth=1 --branch REL1_39 https://github.com/wikimedia/mediawiki-extensions-CategoryTree.git CategoryTree \
 && git clone --depth=1 --branch REL1_39 https://github.com/wikimedia/mediawiki-extensions-TemplateData.git TemplateData \
 && git clone --depth=1 --branch REL1_39 https://github.com/wikimedia/mediawiki-extensions-WikiEditor.git WikiEditor \
 && git clone --depth=1 --branch REL1_39 https://github.com/wikimedia/mediawiki-extensions-TemplateStyles.git TemplateStyles \
 \
 && echo "=== SMW core ===" \
 && git clone --depth=1 --branch 4.2.0 https://github.com/SemanticMediaWiki/SemanticMediaWiki.git \
 && git clone --branch datatables-v2-improvements https://github.com/Knowledge-Wiki/SemanticResultFormats.git SemanticResultFormats \
 && cd SemanticResultFormats \
 && git checkout 5e0ba274c5d60b6dab3aac0e2d9eb433eb59987a \
 && rm -rf extensions \
 && cd .. \
 \
 && echo "=== Forms ===" \
 && git clone --depth=1 --branch REL1_39 https://github.com/wikimedia/mediawiki-extensions-PageForms.git PageForms \
 \
 && echo "=== Maps stack ===" \
 && git clone --depth=1 https://github.com/JeroenDeDauw/Validator.git Validator \
 && git clone --depth=1 https://github.com/JeroenDeDauw/ParamProcessor.git ParamProcessor \
 && git clone --depth=1 https://github.com/ProfessionalWiki/Maps.git Maps \
 && git clone --depth=1 --branch 4.0.0 https://github.com/ProfessionalWiki/ModernTimeline.git ModernTimeline \
 \
 && echo "=== Other ===" \
 && git clone --depth=1 --branch REL1_39 https://github.com/wikimedia/mediawiki-extensions-Widgets.git Widgets

# --------------------------------------------------
# SKINS
# --------------------------------------------------
RUN mkdir -p /var/www/html/skins \
 && cd /var/www/html/skins \
 \
 && echo "=== MediaWiki skins ===" \
 && git clone --depth=1 --branch REL1_39 https://gerrit.wikimedia.org/r/mediawiki/skins/Vector Vector

# --------------------------------------------------
# Custom FINA extensions and skins
# --------------------------------------------------
COPY custom/extensions/Bootstrap /var/www/html/extensions/Bootstrap
COPY custom/extensions/Kma /var/www/html/extensions/Kma

COPY custom/skins/Kma /var/www/html/skins/Kma
COPY custom/skins/Chameleon /var/www/html/skins/Chameleon

RUN ln -s /var/www/html/skins/Chameleon /var/www/html/skins/chameleon
RUN ln -s /var/www/html/skins /var/www/html/Skins

# --------------------------------------------------
# ROOT COMPOSER
# --------------------------------------------------
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --ignore-platform-reqs

RUN rm -rf /var/www/html/extensions/SemanticResultFormats/extensions

# --------------------------------------------------
# CUSTOM EXTENSIONS
# --------------------------------------------------
RUN cd /var/www/html/extensions/Bootstrap \
 && composer install --no-dev --no-interaction --ignore-platform-reqs

# --------------------------------------------------
# EXTENSION COMPOSER
# --------------------------------------------------
RUN set -ex \
 && cd /var/www/html/extensions/SemanticMediaWiki \
 && composer install --no-dev --no-interaction --ignore-platform-reqs \
 \
 && cd /var/www/html/extensions/Maps \
 && composer install --no-dev --no-interaction --ignore-platform-reqs \
 \
 && cd /var/www/html/extensions/TemplateStyles \
 && composer install --no-dev --no-interaction --ignore-platform-reqs

RUN rm -rf \
    /var/www/html/extensions/SemanticResultFormats/vendor \
    /var/www/html/extensions/SemanticResultFormats/composer.lock \
    /var/www/html/extensions/SemanticResultFormats/extensions

# --------------------------------------------------
# PERMISSIONS
# --------------------------------------------------
RUN chown -R www-data:www-data /var/www/html

# --------------------------------------------------
# PHP SETTINGS
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
 && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini

# --------------------------------------------------
# ENTRYPOINT
# --------------------------------------------------
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]

EXPOSE 80

CMD ["apache2-foreground"]
