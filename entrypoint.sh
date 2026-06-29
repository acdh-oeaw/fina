
#!/bin/bash
export PATH="/app/.heroku/php/bin:${PATH}"

cd /app/

# Install dependencies deterministically (never update!)
if [ ! -d "vendor" ]; then
  echo "Running composer install..."
  composer install --no-dev --optimize-autoloader
fi

# Wait until DB is reachable
echo "Waiting for database..."
until php -r "
try {
  new PDO(getenv('DB_DSN'), getenv('DB_USER'), getenv('DB_PASSWORD'));
} catch (Exception \$e) {
  exit(1);
}
"; do
  sleep 5
done

# Run MediaWiki update (safe even if repeated)
echo "Running MediaWiki update..."
php maintenance/update.php || echo "update.php failed, continuing..."

# Start web server
exec heroku-php-apache2 -F fpm_custom.conf

#####################################################################
# Old config
####################################################################

#!/bin/bash
# export PATH="/app/.heroku/php/bin:${PATH}"

# cd /app/
# composer update --lock
#php maintenance/update.php

# Should be executed after coonfiguration changes:
# php extensions/SemanticMediaWiki/maintenance/updateEntityCollation.php 

# Should be executed every 90 days
# php extensions/SemanticMediaWiki/maintenance/setupStore.php 


# php extensions/CirrusSearch/maintenance/UpdateSearchIndexConfig.php  --startOver
# php extensions/CirrusSearch/maintenance/ForceSearchIndex.php --skipLinks —indexOnSkip 
# php maintenance/runJobs.php

## Start web server
# exec heroku-php-apache2 -F fpm_custom.conf
# exec heroku-php-nginx
