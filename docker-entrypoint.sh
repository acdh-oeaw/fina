#!/bin/bash
set -e

cd /var/www/html

# 🔥 FIX: restore semantic extensions (PVC override)
rm -rf /var/www/html/extensions/SemanticMediaWiki
rm -rf /var/www/html/extensions/SemanticResultFormats

ln -s /var/www/html/vendor/mediawiki/semantic-media-wiki /var/www/html/extensions/SemanticMediaWiki
ln -s /var/www/html/vendor/mediawiki/semantic-result-formats /var/www/html/extensions/SemanticResultFormats

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

echo "Running MediaWiki update..."
php maintenance/update.php || true

exec "$@"