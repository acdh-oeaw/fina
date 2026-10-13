# Based on Dalibor's feat/mediawiki-1-43-image, ee1d15bd234a; updated within 1.43 for security fixes.
FROM docker.io/library/mediawiki:1.43.11@sha256:76b3d47b50c4e4a78f82e814917421cb62c49b0c56b4a0f981cd74e2bddd10ac AS dependencies

RUN apt-get update && apt-get install -y --no-install-recommends \
      unzip libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j2 gd zip \
 && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2@sha256:af98f42dfff7c68ba8d53c2164fd9fde1087b7d449514baa38c418b1f6bc4bac /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html
COPY upgrade/1.43/extensions.lock.json upgrade/1.43/requirements.json upgrade/1.43/download-extensions.php upgrade/1.43/prepare-composer.php /opt/fina/
RUN chmod 0755 /var/www/html \
 && php /opt/fina/download-extensions.php \
 && php /opt/fina/prepare-composer.php

FROM dependencies AS runtime
COPY upgrade/1.43/composer.lock /var/www/html/composer.lock
RUN composer install --no-dev --no-interaction --prefer-dist \
 && composer check-platform-reqs --no-dev \
 && composer audit --no-dev \
 && composer clear-cache

RUN printf "memory_limit=512M\nmax_execution_time=360\nmax_input_time=360\nupload_max_filesize=256M\npost_max_size=257M\n" \
      > /usr/local/etc/php/conf.d/zz-fina.ini \
 && printf "opcache.enable=1\nopcache.memory_consumption=128\nopcache.max_accelerated_files=20000\n" \
      > /usr/local/etc/php/conf.d/zz-fina-opcache.ini \
 && a2enmod rewrite \
 && mkdir -p /tmp/mw-cache /tmp/widgets \
 && chown -R www-data:www-data /tmp/mw-cache /tmp/widgets
COPY upgrade/1.43/apache-local.conf /etc/apache2/conf-enabled/fina-local.conf
COPY upgrade/1.43/skins/Kma /var/www/html/skins/Kma
COPY upgrade/1.43/extensions/Kma /var/www/html/extensions/Kma
COPY upgrade/1.43/export-assets.lock.json upgrade/1.43/download-export-assets.php /opt/fina/
RUN php /opt/fina/download-export-assets.php
COPY upgrade/1.43/patches /opt/fina/patches
RUN for patchfile in /opt/fina/patches/*.patch; do patch --batch --forward --fuzz=0 -p1 < "$patchfile" || exit 1; done
COPY customisations/root-files/ /var/www/html/
COPY customisations/LocalSettings.php upgrade/1.43/FinaSettings.php /var/www/html/
COPY upgrade/1.43/migrate.sh /usr/local/bin/fina-migrate
COPY upgrade/1.43/run-jobs.php /usr/local/bin/fina-run-jobs.php
COPY upgrade/1.43/healthcheck.php /usr/local/bin/fina-healthcheck.php
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh /usr/local/bin/fina-migrate
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
