# FINA MediaWiki

FINA is a customized MediaWiki installation developed by the ACDH-CH, Austrian Academy of Sciences.

This repository contains the complete application source code together with a reproducible Docker-based deployment replacing the legacy Herokuish deployment.

## Project Goals

The primary objective of this project is to preserve the current production behaviour while making the deployment reproducible, maintainable and fully documented.

The migration intentionally prioritizes compatibility over modernization.

## Technology Stack

* MediaWiki 1.39 LTS
* PHP 8.1
* Apache
* MySQL
* Semantic MediaWiki 4.2
* Docker

## Deployment Principles

The Docker image contains the complete application.

No MediaWiki extensions are mounted at runtime.

No MediaWiki skins are mounted at runtime.

All dependencies are installed during the image build.

## Repository Structure

```text
.
├── Dockerfile
├── docker-entrypoint.sh
├── LocalSettings.php
├── custom/
│   ├── extensions/
│   └── skins/
└── docs/
    ├── MIGRATION.md
    ├── ARCHITECTURE.md
    └── TECHNICAL_SPECIFICATION.md
```

## Documentation

* `docs/MIGRATION.md` — migration strategy and progress
* `docs/ARCHITECTURE.md` — system architecture
* `docs/TECHNICAL_SPECIFICATION.md` — implementation details, compatibility rules and deployment constraints

## Development Policy

The goal of this repository is to reproduce the existing production deployment.

Behavioral changes, upgrades and refactoring are intentionally kept outside the scope of the migration unless explicitly documented.

## License

See the corresponding license files of MediaWiki and bundled extensions.
