#!/bin/bash
export PATH="/app/.heroku/php/bin:${PATH}"

cd /app/
composer update

# Start web server
exec heroku-php-apache2