#!/bin/bash
set -e

cd /var/www/html

echo "=== INIT: Fix Composer + SMW setup ==="

# 🔥 1. FIX: remove broken SMW autoload (SMW 4.x bug)
if [ -f vendor/composer/autoload_files.php ]; then
  sed -i '/SemanticMediaWiki\/includes\/GlobalFunctions.php/d' vendor/composer/autoload_files.php
fi

if [ -f vendor/composer/autoload_static.php ]; then
  sed -i '/SemanticMediaWiki\/includes\/GlobalFunctions.php/d' vendor/composer/autoload_static.php
fi

# 🔥 2. FIX: restore extensions (PVC override)
echo "Fixing SemanticMediaWiki symlinks (PVC)..."

rm -rf extensions/SemanticMediaWiki
rm -rf extensions/SemanticResultFormats

ln -s /var/www/html/vendor/mediawiki/semantic-media-wiki extensions/SemanticMediaWiki
ln -s /var/www/html/vendor/mediawiki/semantic-result-formats extensions/SemanticResultFormats


echo "Extensions ready"

# ----- DB CONFIG -----
DB_HOST="${MYSQL_HOST:-${MYSQL_SERVER}}"
DB_NAME="${MYSQL_DB}"
DB_USER="${MYSQL_USER}"
DB_PASS="${MYSQL_PASSWORD}"

if [ -z "$DB_HOST" ]; then
  echo "ERROR: MYSQL_SERVER not set"
  exit 1
fi

echo "Waiting for MySQL at $DB_HOST..."

until php -r "
try {
  new PDO('mysql:host=$DB_HOST;dbname=$DB_NAME', '$DB_USER', '$DB_PASS');
} catch (Exception \$e) {
  exit(1);
}
"; do
  sleep 5
done

echo "Database is up"

# 🔥 refresh autoload 
echo "Refreshing Composer autoload..."
composer dump-autoload --no-dev --optimize || true

# ----- MW UPDATE -----
echo "Running MediaWiki update..."
php maintenance/update.php || true

echo "=== INIT DONE ==="

exec "$@"
