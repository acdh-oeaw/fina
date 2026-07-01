FROM php:8.1-apache

ENV MW_VERSION=REL1_39
ENV SMW_VERSION=4.2.0

# --------------------------------------------------
# SYSTEM
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

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --------------------------------------------------
# APACHE
# --------------------------------------------------

RUN a2enmod rewrite \
 && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# --------------------------------------------------
# MEDIAWIKI CORE
# --------------------------------------------------

RUN git clone \
    --depth=1 \
    --recurse-submodules \
    --branch ${MW_VERSION} \
    https://github.com/wikimedia/mediawiki.git \
    /var/www/html

WORKDIR /var/www/html

# --------------------------------------------------
# EXTRA EXTENSIONS
# --------------------------------------------------

RUN cd extensions \
 && git clone --depth=1 --branch REL1_39 \
    https://github.com/wikimedia/mediawiki-extensions-TemplateStyles.git TemplateStyles \
 && git clone --depth=1 --branch REL1_39 \
    https://github.com/wikimedia/mediawiki-extensions-PageForms.git PageForms \
 && git clone --depth=1 --branch REL1_39 \
    https://github.com/wikimedia/mediawiki-extensions-Widgets.git Widgets

# --------------------------------------------------
# SEMANTIC STACK
# --------------------------------------------------

RUN cd extensions \
 && git clone --depth=1 --branch ${SMW_VERSION} \
    https://github.com/SemanticMediaWiki/SemanticMediaWiki.git \
 && git clone \
    --branch datatables-v2-improvements \
    https://github.com/Knowledge-Wiki/SemanticResultFormats.git \
    SemanticResultFormats \
 && cd SemanticResultFormats \
 && git checkout 5e0ba274c5d60b6dab3aac0e2d9eb433eb59987a

# --------------------------------------------------
# MAPS & DEPENDENCIES
# --------------------------------------------------

RUN cd extensions \
 && git clone --depth=1 https://github.com/JeroenDeDauw/Validator.git Validator \
 && git clone --depth=1 https://github.com/JeroenDeDauw/ParamProcessor.git ParamProcessor \
 && git clone --depth=1 https://github.com/ProfessionalWiki/Maps.git Maps \
 && git clone --depth=1 --branch 4.0.0 \
    https://github.com/ProfessionalWiki/ModernTimeline.git ModernTimeline

# --------------------------------------------------
# CUSTOMISATIONS
# --------------------------------------------------

COPY customisations/LocalSettings.php \
     /var/www/html/LocalSettings.php

COPY customisations/extensions/Bootstrap \
     /var/www/html/extensions/Bootstrap

COPY customisations/extensions/Kma \
     /var/www/html/extensions/Kma

COPY customisations/skins/Chameleon \
     /var/www/html/skins/Chameleon

COPY customisations/skins/Kma \
     /var/www/html/skins/Kma

COPY .htaccess \
     /var/www/html/.htaccess

RUN mkdir -p /var/www/html/images

# --------------------------------------------------
# COMPATIBILITY LINKS
# --------------------------------------------------

RUN ln -sf /var/www/html/skins/Chameleon /var/www/html/skins/chameleon \
 && ln -sf /var/www/html/skins /var/www/html/Skins

# --------------------------------------------------
# COMPOSER: ROOT
# --------------------------------------------------

WORKDIR /var/www/html

RUN composer config --no-interaction policy.advisories.block false

RUN composer install \
    --no-dev \
    --no-interaction \
    --ignore-platform-reqs

# --------------------------------------------------
# COMPOSER: EXTENSIONS
# --------------------------------------------------

RUN cd extensions/SemanticMediaWiki \
 && composer install \
    --no-dev \
    --no-interaction \
    --ignore-platform-reqs

RUN cd extensions/SemanticResultFormats \
 && composer install \
    --no-dev \
    --no-interaction \
    --ignore-platform-reqs

RUN cd extensions/Maps \
 && composer install \
    --no-dev \
    --no-interaction \
    --ignore-platform-reqs

RUN cd extensions/TemplateStyles \
 && composer install \
    --no-dev \
    --no-interaction \
    --ignore-platform-reqs

RUN cd extensions/Bootstrap \
 && composer install \
    --no-dev \
    --no-interaction \
    --ignore-platform-reqs

RUN cd extensions/Widgets \
 && composer install \
    --no-dev \
    --no-interaction \
    --ignore-platform-reqs

# --------------------------------------------------
# COMPOSER: SKINS
# --------------------------------------------------

RUN cd skins/Chameleon \
 && composer install \
    --no-dev \
    --no-interaction \
    --ignore-platform-reqs

# --------------------------------------------------
# PHP CONFIG
# --------------------------------------------------

RUN printf "memory_limit=512M\n\
max_execution_time=360\n\
max_input_time=360\n\
upload_max_filesize=256M\n\
post_max_size=257M\n" \
> /usr/local/etc/php/conf.d/media.ini

RUN printf "opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.max_accelerated_files=20000\n" \
> /usr/local/etc/php/conf.d/opcache.ini

# --------------------------------------------------
# PERMISSIONS
# --------------------------------------------------

RUN chown -R www-data:www-data /var/www/html

# --------------------------------------------------
# ENTRYPOINT
# --------------------------------------------------

COPY docker-entrypoint.sh \
     /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]

EXPOSE 80

CMD ["apache2-foreground"]
