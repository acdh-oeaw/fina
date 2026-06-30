#!/bin/bash
set -e

cd /var/www/html

echo "=== INIT: MediaWiki start ==="

# --------------------------------------------------
# DATABASE
# --------------------------------------------------

DB_HOST="${MYSQL_HOST:-${MYSQL_SERVER}}"
DB_NAME="${MYSQL_DB}"
DB_USER="${MYSQL_USER}"
DB_PASS="${MYSQL_PASSWORD}"

if [ -z "$DB_HOST" ]; then
    echo "ERROR: MYSQL_HOST/MYSQL_SERVER not set"
    exit 1
fi

echo "Waiting for MySQL: $DB_HOST"

until php -r "
try {
    new PDO(
        'mysql:host=$DB_HOST;dbname=$DB_NAME',
        '$DB_USER',
        '$DB_PASS'
    );
} catch (Exception \$e) {
    exit(1);
}
"; do
    echo "Database unavailable, retrying..."
    sleep 5
done

echo "Database available"

# --------------------------------------------------
# MEDIAWIKI UPDATE
# --------------------------------------------------

if [ -f maintenance/update.php ]; then
    echo "Running MediaWiki update"
    php maintenance/update.php --quick
fi

echo "=== INIT DONE ==="

exec "$@"