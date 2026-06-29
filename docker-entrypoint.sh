#!/bin/bash
set -e

cd /var/www/html

# Build DSN from K8s secrets
DB_HOST="${MYSQL_HOST:-mysql}"
DB_NAME="${MYSQL_DB}"
DB_USER="${MYSQL_USER}"
DB_PASS="${MYSQL_PASSWORD}"

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

# Run MediaWiki update (safe if repeated)
echo "Running MediaWiki update..."
php maintenance/update.php || true

# Start web server
exec "$@"