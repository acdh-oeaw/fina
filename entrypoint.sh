#!/bin/bash
export PATH="/app/.heroku/php/bin:${PATH}"

cd /app/
composer update --lock
php maintenance/update.php

# Should be executed after coonfiguration changes:
# php extensions/SemanticMediaWiki/maintenance/updateEntityCollation.php 

# Should be executed every 90 days
# php extensions/SemanticMediaWiki/maintenance/setupStore.php 


# php extensions/CirrusSearch/maintenance/UpdateSearchIndexConfig.php  --startOver
# php extensions/CirrusSearch/maintenance/ForceSearchIndex.php --skipLinks —indexOnSkip 
# php maintenance/runJobs.php

## Start web server
exec heroku-php-apache2 -F fpm_custom.conf
# exec heroku-php-nginx
