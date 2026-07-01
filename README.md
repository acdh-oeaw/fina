# FINA Wiki Docker Deployment

## Overview

This repository contains the infrastructure, configuration, and customizations required to build and run the FINA Wiki platform.

Unlike a traditional MediaWiki repository, MediaWiki core and most third-party extensions are **not stored in Git**. Instead, they are downloaded directly from their official upstream repositories during the Docker image build process.

The goal of this approach is to:

- Keep the repository small and maintainable.
- Avoid storing third-party code.
- Simplify upgrades of MediaWiki and extensions.
- Make the deployment reproducible.
- Keep only FINA-specific customizations under version control.

---

## Repository Structure

```text
.
├── Dockerfile
├── docker-entrypoint.sh
├── .htaccess
├── README.md
└── customisations/
    ├── LocalSettings.php
    ├── extensions/
    │   ├── Bootstrap/
    │   └── Kma/
    ├── skins/
    │   ├── Chameleon/
    │   └── Kma/
    ├── root-files/
    │   ├── favicon.png
    │   ├── fina-logo.png
    │   ├── footer-logo.png
    │   ├── google17603f5f7c6b9568.html
    │   └── KnowledgeWiki.png
    ├── images/
    ├── resources/
    └── ...
```

---

## Current Stack

| Component | Version |
|---|---|
| MediaWiki | 1.39.17 (REL1_39) |
| PHP | 8.1 |
| Semantic MediaWiki | 4.2.0 |

---

## Build Process

The Docker image is self-contained and downloads all required MediaWiki components during the build.

### MediaWiki Core

MediaWiki is cloned from the official MediaWiki repository using the REL1_39 branch.

### Standard Extensions (bundled/core)

The following extensions are loaded from MediaWiki core or downloaded during build:

- ParserFunctions
- Scribunto
- Cite
- CategoryTree
- TemplateData
- WikiEditor
- TemplateStyles
- CiteThisPage
- CodeEditor
- ConfirmEdit
- Gadgets
- ImageMap
- InputBox
- Interwiki
- Nuke
- OATHAuth
- PageImages
- PdfHandler
- Poem
- Renameuser
- ReplaceText
- SecureLinkFixer
- SyntaxHighlight_GeSHi
- TextExtracts

### Semantic MediaWiki Stack

- SemanticMediaWiki (4.2.0)
- SemanticResultFormats (custom fork for MW 1.39 compatibility)

### Maps Stack

- Validator
- ParamProcessor
- Maps
- ModernTimeline

### Form and Data Extensions

- PageForms
- Widgets
- ExternalData
- DataTransfer

### Additional Compatible Extensions (MW 1.39)

- NativeSvgHandler
- LinkTarget
- AdminLinks
- RSS
- MyVariables
- Variables
- UrlGetParameters
- UserFunctions
- RightFunctions
- Mpdf
- CookieWarning
- Popups
- Lockdown
- CodeMirror
- Lingo

### Disabled Extensions (incompatible with MW 1.39)

The following extensions are installed but **disabled** in LocalSettings.php because they require MediaWiki ≥1.40–1.46 and/or SMW ≥6.0–7.0:

| Extension | Requires MW | Requires SMW |
|---|---|---|
| HeaderTabs | ≥1.40 | - |
| TitleIcon | ≥1.43 | - |
| DeleteBatch | ≥1.46 | - |
| SimpleBatchUpload | ≥1.43 | - |
| ImportUsers | ≥1.46 | - |
| RottenLinks | ≥1.44 | - |
| MatomoAnalytics | ≥1.43 | - |
| SemanticCompoundQueries | ≥1.43 | ≥7.0 |
| SemanticExtraSpecialProperties | ≥1.43 | ≥7.0 |
| SemanticMetaTags | ≥1.43 | ≥7.0 |
| SemanticCite | ≥1.43 | ≥7.0 |
| SemanticDependencyUpdater | ≥1.43 | ≥6.0 |
| SemanticGlossary | ≥1.43 | ≥7.0 |
| SemanticDrilldown | ≥1.43 | - |
| Network | ≥1.43 | - |
| Mermaid | ≥1.43 | - |
| KnowledgeGraph | ≥1.43 | - |

These extensions can be re-enabled after upgrading MediaWiki to ≥1.43 and SMW to ≥7.0.

### Skins

- Vector
- Chameleon (customized)
- Kma (customized, default skin)

---

## Root-Level Files

The following files are stored in `customisations/root-files/` and copied to the MediaWiki web root (`/var/www/html/`) during image build:

- `favicon.png` — site favicon
- `fina-logo.png` — main site logo
- `footer-logo.png` — footer logo
- `google17603f5f7c6b9568.html` — Google Search Console verification
- `KnowledgeWiki.png` — Knowledge Wiki logo

---

## Customizations

Only FINA-specific code should be stored in this repository.

Examples include:

- LocalSettings.php
- Custom MediaWiki extensions
- Custom MediaWiki skins
- Images and logos
- Static assets
- CSS customizations
- JavaScript customizations
- Deployment-specific configuration

Third-party code that can be obtained from upstream repositories should not be committed to Git.

---

## Docker Entrypoint

The container startup sequence is implemented in:

```text
docker-entrypoint.sh
```

During startup the container:

1. Waits for the database to become available.
2. Verifies database connectivity.
3. Executes MediaWiki database updates (`maintenance/update.php`).
4. Runs Semantic MediaWiki setup tasks **in the background**:
   - `setupStore.php` — ensures SMW database tables are up to date.
   - `updateEntityCollation.php` — updates entity collation settings.
5. Starts Apache.

The SMW setup tasks run in the background to avoid delaying container startup and health check failures. Apache starts immediately while SMW tasks complete asynchronously.

---

## Database Configuration

The container expects the following environment variables:

```bash
MYSQL_SERVER
MYSQL_DB
MYSQL_USER
MYSQL_PASSWORD
```

Additional optional variables:

```bash
PUBLIC_URL          # defaults to https://fina.oeaw.ac.at
MW_SECRET_KEY       # MediaWiki secret key
MAIL_SMTP_HOST
MAIL_SMTP_USER
MAIL_SMTP_PASSWORD
EMERGENCY_CONTACT
```

---

## Composer Dependencies

Composer is executed during the image build process.

Dependencies are installed for:

- MediaWiki core
- SemanticMediaWiki
- Maps
- TemplateStyles
- Bootstrap

Composer is executed with production-friendly options:

```bash
--no-dev
--no-interaction
--prefer-dist
--optimize-autoloader
```

---

## SemanticResultFormats Compatibility

The current deployment uses a custom SemanticResultFormats fork and commit.

Additional build-time modifications are applied to:

- Remove bundled SMW components.
- Register missing autoload classes.
- Register missing `SRF\` namespaces.
- Ensure compatibility with MediaWiki 1.39.

These changes are performed automatically during image creation.

---

## Local Development

Build the image:

```bash
docker build -t fina-wiki .
```

Run locally:

```bash
docker run -p 8080:80 \
  -e MYSQL_SERVER=host.docker.internal \
  -e MYSQL_DB=fina \
  -e MYSQL_USER=root \
  -e MYSQL_PASSWORD=secret \
  fina-wiki
```

Open:

```text
http://localhost:8080
```

---

## Deployment Workflow

Typical workflow:

```bash
git checkout -b feature/my-change
```

Apply changes.

```bash
git add .
git commit -m "Describe change"
git push
```

Build and deploy a new Docker image.

The container automatically executes MediaWiki updates and SMW setup during startup.

---

## Upgrade Strategy

### Minor Updates (within MW 1.39)

1. Rebuild the Docker image (pulls latest 1.39.x).
2. Deploy and verify.

### Major Upgrade (MW 1.39 → 1.43+)

1. Update the MediaWiki branch in the Dockerfile to REL1_43.
2. Update SMW to ≥7.0 via Composer.
3. Re-enable the 17 currently disabled extensions in LocalSettings.php.
4. Update extension branches to match MW 1.43 compatibility.
5. Build a new image.
6. Deploy to a test environment.
7. Run `maintenance/update.php`.
8. Verify all extensions load correctly.
9. Deploy to production.

Because the repository contains only custom code, upgrading MediaWiki is significantly simpler than maintaining a full application checkout.

---

## Backup Strategy

Before major upgrades:

- Backup the MediaWiki database.
- Backup uploaded files (images directory).
- Verify restore procedures.
- Test the upgrade in a non-production environment.

---

## Contributing

When adding functionality:

- Prefer upstream repositories whenever possible.
- Store only FINA-specific code in this repository.
- Avoid committing vendor directories.
- Avoid committing MediaWiki core.
- Keep Docker builds reproducible.
- Verify extension compatibility with MW 1.39 before enabling.

---

## License

This repository contains deployment-specific configuration and customizations for the FINA Wiki platform.

All third-party components remain subject to their respective upstream licenses.