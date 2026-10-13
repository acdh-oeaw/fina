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
`PUBLIC_URL` defaults to `https://fina.oeaw.ac.at`. Keep credentials outside Git.
Mount existing uploads at `/var/www/html/images`; files are served through
`img_auth.php`.

SMTP uses `MAIL_SMTP_HOST`, `MAIL_SMTP_USER`, `MAIL_SMTP_PASSWORD` and
`EMERGENCY_CONTACT` (port 25). Without an SMTP host, mail is disabled.
`FINA_ENV=local` disables mail and enables diagnostic details for local testing.

## Database upgrade

The image uses the instance’s existing database and uploads. It contains no
wiki-page replacements or backup data. Startup runs the web server;
database migration is explicit.

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

`$wgJobRunRate` is zero. Schedule a worker without overlapping invocations:

```sh
php maintenance/run.php runJobs --maxjobs 25 --maxtime 55 --memory-limit 512M
```

The existing main-branch workflow deploys on merge; coordinate the migration
before merging.
