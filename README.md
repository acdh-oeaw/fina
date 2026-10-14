# FINA MediaWiki 1.43

Based on Dalibor’s `feat/mediawiki-1-43-image`, with MediaWiki 1.43.11, PHP 8.3,
Semantic MediaWiki 7.3.1, Semantic Result Formats 6.0.0 and FINA’s Kma skin.
Extensions and dependencies are pinned and installed during the image build.

```sh
docker build -t fina:1.43 .
```

FINA’s skin, extension adaptations and dependency locks are in `upgrade/1.43/`.
Configuration is in `customisations/LocalSettings.php` and
`upgrade/1.43/FinaSettings.php`; branding assets are in
`customisations/root-files/`.

## Runtime configuration

Set `MYSQL_SERVER`, `MYSQL_DB`, `MYSQL_USER`, `MYSQL_PASSWORD` and `MW_SECRET_KEY`.

SMTP uses `MAIL_SMTP_HOST`, `MAIL_SMTP_USER`, `MAIL_SMTP_PASSWORD` and
`EMERGENCY_CONTACT` (port 25). Without an SMTP host, mail is disabled.
`FINA_ENV=local` disables mail and enables diagnostic details for local testing.

Temporary files and UUID state use `/tmp/mw-tmp`, independently of the uploads
volume. Startup and readiness probes query the local API and check one bounded
`allpages` result; liveness uses a TCP check.

## Database upgrade

The image uses the instance’s existing database and uploads. It contains no
wiki-page replacements or backup data. Full schema/data migration is explicit.
Deployment automatically completes a pending SMW collation update as described below.

Before adopting this configuration, check that the removed duplicate FINA
namespaces 3000/3001 contain no pages in the target database:

```sql
SELECT page_namespace, COUNT(*) FROM fwpage
WHERE page_namespace IN (3000, 3001) GROUP BY page_namespace;
```

If rows are returned, review the namespace mapping first. With a matching
database/uploads backup, writes and other workers stopped, run inside the new
container as `www-data`:

```sh
FINA_MIGRATION_WORKERS=2 /usr/local/bin/fina-migrate
```

This updates schema and semantic indexes. Check the migration output and empty
job queue before reopening writes. Rollback requires the paired pre-upgrade
database/uploads backup and previous image.

The deployment uses one replica and the `Recreate` strategy, so the old web pod
stops before the new one starts. Its `fina-smw-collation` init container runs
`fina-migrate --collation-only` as `www-data`, using the same image and environment
secret as the web container. This checks that the SMW schema is ready, repairs
pending or outdated semantic sort keys, and verifies completion before starting
the web server. It skips the update when the collation is already current.

This step does not rerun the full schema/data migration or edit wiki pages.
Keep a database backup and pause any separately managed job workers for the
deployment. The site is unavailable during the repair. If it fails, deployment
cannot become ready; inspect `kubectl logs <pod> -c fina-smw-collation` and resolve
the reported error. Check `Special:PendingTaskList` for any other outstanding tasks.

`$wgJobRunRate` is zero. Schedule a worker without overlapping invocations:

```sh
php maintenance/run.php runJobs --maxjobs 25 --maxtime 55 --memory-limit 512M
```

The existing main-branch workflow deploys on merge; coordinate the migration
before merging.

## GitHub Actions maintenance

Run **MediaWiki maintenance** from the Actions tab on the default branch. Choose
`collation`, `update`, or `run-jobs`, then provide the full FINA image reference.
The workflow pins `kubectl` to `v1.36.4`, matching the RKE2 cluster. The
organization must provide the `KUBE_CONFIG` secret containing the kubeconfig,
and the runner needs access to the cluster.

Production is deployed only from `main`. After the main-branch deployment
succeeds, create a version tag on that deployed commit to publish an alias for
its existing image; this does not rebuild or deploy. Use the MediaWiki base
version from `Dockerfile`, for example:

```sh
git tag -a v1.43.11 <deployed-commit> -m "FINA release for MediaWiki 1.43.11"
git push origin v1.43.11
```

The `Release image tag` workflow publishes
`ghcr.io/acdh-oeaw/fina/fina:1.43.11` from the commit's existing SHA-tagged
image. For FINA-only updates on the same MediaWiki version, use a suffix such
as `v1.43.11-fina.1`. Use the published image reference for maintenance jobs.

`collation` and `update` suspend the queue and stop the web Deployment; both
remain stopped after the job, including on success. Verify and deploy the
intended release before restoring web replicas and resuming the queue.
`run-jobs` leaves web running. If the queue CronJob exists, suspend it first;
the workflow accepts a missing CronJob and still checks that the image matches
the deployed web image. Inspect the job logs in the Actions run.
