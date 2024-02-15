#!/bin/bash
export PATH="/app/.heroku/php/bin:${PATH}"

# Start web server
exec heroku-php-apache2