FROM php:8.1-apache

ENV MW_VERSION=REL1_39
ENV SMW_VERSION=4.2.0

# --------------------------------------------------
# SYSTEM + PHP EXTENSIONS (single layer)
# --------------------------------------------------

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip curl lua5.1 \
    libicu-dev libzip-dev libpng-dev libxml2-dev \
    libonig-dev libfreetype6-dev libjpeg62-turbo-dev \
 && docker-php-ext-install intl pdo pdo_mysql mysqli zip gd xml calendar \
 && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

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

RUN git clone --depth=1 --recurse-submodules --branch ${MW_VERSION} \
    https://github.com/wikimedia/mediawiki.git /var/www/html \
 && find /var/www/html -name ".git" -type d -exec rm -rf {} + 2>/dev/null; true

WORKDIR /var/www/html

# --------------------------------------------------
# ALL EXTENSIONS (single layer, remove .git dirs)
# --------------------------------------------------

RUN cd extensions \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-TemplateStyles.git TemplateStyles \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-PageForms.git PageForms \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-Widgets.git Widgets \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-Elastica.git Elastica \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-CirrusSearch.git CirrusSearch \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-AdvancedSearch.git AdvancedSearch \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-CookieWarning.git CookieWarning \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-Popups.git Popups \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-Lockdown.git Lockdown \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-CodeMirror.git CodeMirror \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-Lingo.git Lingo \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-HeaderTabs.git HeaderTabs \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-TitleIcon.git TitleIcon \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-NativeSvgHandler.git NativeSvgHandler \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-LinkTarget.git LinkTarget \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-ExternalData.git ExternalData \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-DataTransfer.git DataTransfer \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-DeleteBatch.git DeleteBatch \
 && git clone --depth=1 -b 2.0.1 https://github.com/ProfessionalWiki/SimpleBatchUpload.git SimpleBatchUpload \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-ImportUsers.git ImportUsers \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-AdminLinks.git AdminLinks \
 && git clone --depth=1 -b REL1_39 https://github.com/miraheze/RottenLinks.git RottenLinks \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-RSS.git RSS \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-MyVariables.git MyVariables \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-Variables.git Variables \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-UrlGetParameters.git UrlGetParameters \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-UserFunctions.git UserFunctions \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-Mpdf.git Mpdf \
 && git clone --depth=1 -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-RightFunctions.git RightFunctions \
 # MatomoAnalytics - DISABLED (no MW 1.39 compatible version) \
 # && git clone --depth=1 ... MatomoAnalytics \
 && git clone --depth=1 -b ${SMW_VERSION} https://github.com/SemanticMediaWiki/SemanticMediaWiki.git \
 && git clone -b datatables-v2-improvements https://github.com/Knowledge-Wiki/SemanticResultFormats.git SemanticResultFormats \
 && cd SemanticResultFormats && git checkout 5e0ba274c5d60b6dab3aac0e2d9eb433eb59987a && cd .. \
 && git clone --depth=1 -b 2.2.0 https://github.com/SemanticMediaWiki/SemanticCompoundQueries.git \
 && git clone --depth=1 -b 3.0.4 https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties.git \
 && git clone --depth=1 -b 2.0.0 https://github.com/SemanticMediaWiki/SemanticMetaTags.git \
 && git clone --depth=1 -b 3.0.0 https://github.com/SemanticMediaWiki/SemanticCite.git \
 && git clone --depth=1 -b 2.0.0 https://github.com/gesinn-it-pub/SemanticDependencyUpdater.git \
 && git clone --depth=1 -b 3.0.0 https://github.com/SemanticMediaWiki/SemanticGlossary.git \
 # SemanticDrilldown - DISABLED (no MW 1.39 compatible version) \
 # && git clone --depth=1 ... SemanticDrilldown \
 && git clone --depth=1 -b 2.2.0 https://github.com/SemanticMediaWiki/KnowledgeGraph.git \
 && git clone --depth=1 https://github.com/JeroenDeDauw/Validator.git Validator \
 && git clone --depth=1 https://github.com/JeroenDeDauw/ParamProcessor.git ParamProcessor \
 && git clone --depth=1 -b 10.3.0 https://github.com/ProfessionalWiki/Maps.git Maps \
 && git clone --depth=1 -b 1.2.2 https://github.com/ProfessionalWiki/ModernTimeline.git ModernTimeline \
 && git clone --depth=1 -b 1.1.0 https://github.com/ProfessionalWiki/Network.git \
 && git clone --depth=1 -b 3.1.0 https://github.com/SemanticMediaWiki/Mermaid.git \
 && find . -name ".git" -type d -exec rm -rf {} + 2>/dev/null; true

# --------------------------------------------------
# CUSTOMISATIONS
# --------------------------------------------------

COPY customisations/LocalSettings.php /var/www/html/LocalSettings.php
COPY customisations/extensions/Bootstrap /var/www/html/extensions/Bootstrap
COPY customisations/extensions/Kma /var/www/html/extensions/Kma
COPY customisations/skins/Chameleon /var/www/html/skins/Chameleon
COPY customisations/skins/Kma /var/www/html/skins/Kma
COPY .htaccess /var/www/html/.htaccess

RUN mkdir -p /var/www/html/images \
 && ln -sf /var/www/html/skins/Chameleon /var/www/html/skins/chameleon \
 && ln -sf /var/www/html/skins /var/www/html/Skins

# --------------------------------------------------
# ALL COMPOSER INSTALLS (separate steps for debugging)
# --------------------------------------------------

RUN composer config --no-interaction policy.advisories.block false \
 && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN cd extensions/SemanticMediaWiki && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN cd extensions/SemanticResultFormats && composer install --no-dev --no-interaction --ignore-platform-reqs \
 && rm -rf extensions/SemanticMediaWiki \
 && composer dump-autoload --no-interaction
RUN cd extensions/Maps && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN cd extensions/TemplateStyles && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN cd extensions/Bootstrap && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN cd extensions/Widgets && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN cd extensions/Elastica && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN cd extensions/CirrusSearch && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN cd extensions/Mpdf && composer config --no-interaction policy.advisories.block false \
 && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN cd extensions/SemanticCite && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN cd extensions/SemanticGlossary && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN cd extensions/ExternalData && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN cd extensions/RSS && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN cd extensions/KnowledgeGraph && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN cd skins/Chameleon && composer install --no-dev --no-interaction --ignore-platform-reqs
RUN composer clear-cache && rm -rf /root/.composer/cache /tmp/*

# --------------------------------------------------
# PHP CONFIG
# --------------------------------------------------

RUN printf "memory_limit=512M\nmax_execution_time=360\nmax_input_time=360\nupload_max_filesize=256M\npost_max_size=257M\n" \
    > /usr/local/etc/php/conf.d/media.ini \
 && printf "opcache.enable=1\nopcache.memory_consumption=128\nopcache.max_accelerated_files=20000\n" \
    > /usr/local/etc/php/conf.d/opcache.ini

# --------------------------------------------------
# PERMISSIONS & ENTRYPOINT
# --------------------------------------------------

RUN chown -R www-data:www-data /var/www/html

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
EXPOSE 80
CMD ["apache2-foreground"]
