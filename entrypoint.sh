#!/bin/bash
export PATH="/app/.heroku/php/bin:${PATH}"

cd /app/
composer update
php maintenance/update.php
# php extensions/SemanticMediaWiki/maintenance/updateEntityCollation.php 


# php extensionsCirrusSearch/maintenance/UpdateSearchIndexConfig.php  --startOver
# php extensions/CirrusSearch/maintenance/ForceSearchIndex.php --skipLinks —indexOnSkip 
# php maintenance/runJobs.php

# Start web server
exec heroku-php-apache2