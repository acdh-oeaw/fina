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
    ├── images/
    ├── resources/
    └── ...
```

---

## Build Process

The Docker image is self-contained and downloads all required MediaWiki components during the build.

### MediaWiki Core

MediaWiki is cloned from the official MediaWiki repository using the configured release branch.

### Standard Extensions

The following extensions are downloaded automatically during image build:

- ParserFunctions
- Scribunto
- Cite
- CategoryTree
- TemplateData
- WikiEditor
- TemplateStyles
- PageForms
- Widgets

### Semantic MediaWiki Stack

The semantic stack includes:

- SemanticMediaWiki
- SemanticResultFormats

Additional compatibility fixes are applied automatically during image build to ensure proper operation with the selected MediaWiki version.

### Maps Stack

The following extensions are installed automatically:

- Validator
- ParamProcessor
- Maps
- ModernTimeline

### Skins

The following skins are installed:

- Vector
- Chameleon (customized)
- Kma (customized)

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
3. Executes MediaWiki database updates.
4. Starts Apache.

This ensures that database schema changes are applied automatically during deployment.

---

## Database Configuration

The container expects the following environment variables:

```bash
MYSQL_HOST
MYSQL_DB
MYSQL_USER
MYSQL_PASSWORD
```

Alternatively:

```bash
MYSQL_SERVER
```

may be used instead of `MYSQL_HOST`.

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
docker run -p 8080:80 fina-wiki
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

The container automatically executes MediaWiki updates during startup.

---

## Upgrade Strategy

To upgrade MediaWiki:

1. Update the MediaWiki branch or tag in the Dockerfile.
2. Update extension branches if required.
3. Build a new image.
4. Deploy to a test environment.
5. Verify MediaWiki maintenance updates.
6. Deploy to production.

Because the repository contains only custom code, upgrading MediaWiki is significantly simpler than maintaining a full application checkout.

---

## Backup Strategy

Before major upgrades:

- Backup the MediaWiki database.
- Backup uploaded files.
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

---

## License

This repository contains deployment-specific configuration and customizations for the FINA Wiki platform.

All third-party components remain subject to their respective upstream licenses.
