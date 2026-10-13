#!/bin/sh
# Run explicitly inside the NEW image, with production writes and other workers stopped.
# Back up the database and uploads first. This script does not take backups for you.
set -eu
cd /var/www/html
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
