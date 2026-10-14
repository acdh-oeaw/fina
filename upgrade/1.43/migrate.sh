#!/bin/sh
# Full migration is explicit; deployment runs --collation-only before starting the web pod.
# Use the NEW image with production writes and other workers stopped.
# Back up the database and uploads first. This script does not take backups for you.
set -eu
cd /var/www/html
if [ "$#" -gt 1 ] || { [ "$#" -eq 1 ] && [ "$1" != '--collation-only' ]; }; then
    echo 'Usage: fina-migrate [--collation-only]' >&2
    exit 2
fi

# Finish an outstanding collation task without repeating the schema/data rebuild.
# Deployment uses this in an init container; subsequent starts skip completed work.
if [ "${1:-}" = '--collation-only' ]; then
    check_status=0
    php maintenance/run.php eval <<'PHP' || check_status=$?
try { $check = new SMW\Maintenance\MaintenanceCheck(); $ready = $check->canExecute(); } catch (Throwable $e) { fwrite(STDERR, $e->getMessage() . "\n"); exit(1); }
if (!$ready) { fwrite(STDERR, $check->getMessage()); exit(1); }
try { $setup = SMW\Services\ServicesFactory::getInstance()->singleton('SetupFile'); $pending = $setup->get(SMW\SetupFile::INCOMPLETE_TASKS) ?? []; $collation = $setup->get(SMW\SetupFile::ENTITY_COLLATION); } catch (Throwable $e) { fwrite(STDERR, $e->getMessage() . "\n"); exit(1); }
if (!isset($pending['smw-updateentitycollation-incomplete']) && $collation === $smwgEntityCollation) { echo "SMW collation is already current; skipping repair.\n"; exit(10); }
PHP
    case "$check_status" in
        0) ;;
        10) exit 0 ;;
        *) exit "$check_status" ;;
    esac
    php maintenance/run.php SemanticMediaWiki:updateEntityCollation
    php maintenance/run.php eval <<'PHP'
try { $setup = SMW\Services\ServicesFactory::getInstance()->singleton('SetupFile'); $pending = $setup->get(SMW\SetupFile::INCOMPLETE_TASKS) ?? []; $collation = $setup->get(SMW\SetupFile::ENTITY_COLLATION); } catch (Throwable $e) { fwrite(STDERR, $e->getMessage() . "\n"); exit(1); }
if (isset($pending['smw-updateentitycollation-incomplete']) || $collation !== $smwgEntityCollation) { fwrite(STDERR, "SMW collation update is still incomplete.\n"); exit(1); }
echo "SMW collation update verified.\n";
PHP
    exit 0
fi

php maintenance/run.php update --quick
php maintenance/run.php SemanticMediaWiki:updateEntityCollation
php maintenance/run.php SemanticMediaWiki:rebuildData --force-update --use-job --report-runtime
# SMW keeps service references that become invalid after MW's --procs forks.
# Start independent PHP processes instead; the database queue claims jobs atomically.
workers=${FINA_MIGRATION_WORKERS:-2}
case "$workers" in 1|2|3|4) ;; *) echo 'Use 1 to 4 migration workers.' >&2; exit 1 ;; esac
pids=''
i=0
while [ "$i" -lt "$workers" ]; do
    php /usr/local/bin/fina-run-jobs.php smw.update &
    pids="$pids $!"
    i=$((i + 1))
done
failed=0
for pid in $pids; do wait "$pid" || failed=1; done
[ "$failed" -eq 0 ]
php /usr/local/bin/fina-run-jobs.php
php maintenance/run.php SemanticMediaWiki:rebuildPropertyStatistics
php maintenance/run.php showJobs --group
printf '%s\n' 'Check that the queue is empty and the migration log contains no failed jobs before reopening writes.'
