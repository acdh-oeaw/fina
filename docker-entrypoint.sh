#!/bin/bash
set -e

cd /var/www/html

echo "=== INIT: MediaWiki start ==="

# -----------------------
# ✅ Ensure SMW deps (PVC safety)
# -----------------------
if [ -d extensions/SemanticMediaWiki ] && [ ! -d extensions/SemanticMediaWiki/vendor ]; then
  echo "Installing SemanticMediaWiki dependencies..."
  (cd extensions/SemanticMediaWiki && composer install --no-dev --no-interaction)
fi

if [ -d extensions/SemanticResultFormats ] && [ ! -d extensions/SemanticResultFormats/vendor ]; then
  echo "Installing SemanticResultFormats dependencies..."
  (cd extensions/SemanticResultFormats && composer install --no-dev --no-interaction)
fi

# -----------------------
# ✅ DB CONFIG
# -----------------------
DB_HOST="${MYSQL_HOST:-${MYSQL_SERVER}}"
DB_NAME="${MYSQL_DB}"
DB_USER="${MYSQL_USER}"
DB_PASS="${MYSQL_PASSWORD}"

if [ -z "$DB_HOST" ]; then
  echo "ERROR: MYSQL_SERVER or MYSQL_HOST not set"
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

# -----------------------
# ✅ MediaWiki update (idempotent)
# -----------------------
echo "Running MediaWiki update..."
php maintenance/update.php || true

echo "=== INIT DONE ==="

# -----------------------
# ✅ Start Apache
# -----------------------
exec "$@"