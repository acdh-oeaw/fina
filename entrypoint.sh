#!/bin/bash
export PATH="/app/.heroku/php/bin:${PATH}"

cd /app/
composer update
php maintenance/update.php
# php extensions/SemanticMediaWiki/maintenance/updateEntityCollation.php 


# php extensions/CirrusSearch/maintenance/UpdateSearchIndexConfig.php  --startOver
# php extensions/CirrusSearch/maintenance/ForceSearchIndex.php --skipLinks —indexOnSkip 
# php maintenance/runJobs.php

sed -i -r -e 's|max_execution_time = 30|max_execution_time = 300|g' /app/.heroku/php/etc/php/php.ini

# Start web server
exec heroku-php-apache2 -F fpm_custom.conf
# exec heroku-php-nginx
