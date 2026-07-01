#!/bin/bash

set -u

cd /var/www/html

echo "=== INIT: MediaWiki start ==="

# --------------------------------------------------
# DATABASE
# --------------------------------------------------

DB_HOST="${MYSQL_HOST:-${MYSQL_SERVER:-}}"
DB_NAME="${MYSQL_DB:-}"
DB_USER="${MYSQL_USER:-}"
DB_PASS="${MYSQL_PASSWORD:-}"

if [ -z "$DB_HOST" ]; then
    echo "ERROR: MYSQL_HOST or MYSQL_SERVER not set"
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
    echo "Database not available yet..."
    sleep 5
done

echo "Database available"

# --------------------------------------------------
# MEDIAWIKI UPDATE
# --------------------------------------------------

if [ -f maintenance/update.php ]; then
    echo "Running MediaWiki update..."

    if php maintenance/update.php --quick; then
        echo "MediaWiki update completed successfully"
    else
        RET=$?
        echo "WARNING: MediaWiki update failed with exit code $RET"
        echo "Container will continue starting for debugging purposes"
    fi
fi

# --------------------------------------------------
# SEMANTIC MEDIAWIKI SETUP (background)
# --------------------------------------------------

if [ -f extensions/SemanticMediaWiki/maintenance/setupStore.php ]; then
    (
        echo "Running SMW setupStore (background)..."
        php maintenance/runScript.php extensions/SemanticMediaWiki/maintenance/setupStore.php || echo "WARNING: SMW setupStore failed"
        echo "Running SMW updateEntityCollation (background)..."
        php maintenance/runScript.php extensions/SemanticMediaWiki/maintenance/updateEntityCollation.php || echo "WARNING: SMW updateEntityCollation failed"
        echo "SMW setup complete"
    ) &
fi

echo "=== INIT DONE ==="

exec "$@"