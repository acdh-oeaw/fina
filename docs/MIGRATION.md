# FINA MediaWiki Migration Guide

**Project:** FINA MediaWiki Deployment Refactoring

**Repository:** https://github.com/acdh-oeaw/fina

**Migration Branch:** `migration/docker-refactor`

**Status:** Work in Progress

---

# 1. Purpose

This document describes the migration of the FINA MediaWiki deployment from the legacy Herokuish-based deployment to a fully reproducible Docker-based deployment.

The primary objective is **not** to modernize the application, but to reproduce the existing production environment as accurately as possible while making the deployment deterministic, maintainable and fully documented.

The migration is performed incrementally to minimize production risk.

---

# 2. Goals

The migration has the following goals.

## Functional Goals

* preserve existing production behaviour
* preserve database compatibility
* preserve Semantic MediaWiki functionality
* preserve custom extensions
* preserve custom skins
* preserve existing page rendering
* preserve existing semantic queries

## Technical Goals

* remove Herokuish completely
* replace legacy build process with Docker
* eliminate mounted `extensions`
* eliminate mounted `skins`
* make every deployment reproducible
* pin every dependency
* fully document custom components
* simplify maintenance

---

# 3. Existing Production Architecture

The original deployment consists of:

* MediaWiki 1.39
* PHP 8.1
* Apache
* MySQL
* Semantic MediaWiki
* several custom extensions
* several custom skins

Historically the deployment relied on:

* mounted `extensions/`
* mounted `skins/`
* Herokuish buildpacks
* manually managed extension versions

Over the years multiple historical copies of extensions accumulated inside the repository.

Examples:

* SemanticResultFormats-*
* SemanticResultFormatsOLD
* SemanticResultFormatsORG
* LingoOld
* SemanticMetaTagsOld
* SemanticGlossaryOld

These directories are **not** considered part of the production deployment.

Only the actively loaded extensions are migrated.

---

# 4. Problems in the Legacy Deployment

The previous deployment suffers from several long-term maintenance issues.

## Mounted Extensions

Extensions were mounted into the container.

Problems:

* unknown versions
* difficult upgrades
* impossible to reproduce locally
* dependency drift

---

## Mounted Skins

The same applies to skins.

Production depended on mounted directories instead of image contents.

---

## Herokuish

Herokuish is deprecated.

Problems:

* difficult PHP upgrades
* unsupported buildpacks
* unpredictable builds

---

## Historical Copies

Numerous backup directories exist.

Examples:

* SemanticResultFormats-*
* LingoOld
* ElasticaOld
* SemanticMetaTagsOld

These are excluded from the migration.

---

## Missing Documentation

Several production-specific customizations existed without documentation.

Examples include:

* SRF namespace patch
* legacy Validator loading
* custom Bootstrap extension
* custom Kma skin
* custom SemanticResultFormats fork

---

# 5. Migration Strategy

The migration follows one principle:

> Reproduce production first.
> Improve afterwards.

Behaviour must remain identical before any cleanup or modernization is attempted.

---

# 6. Target Deployment

The target deployment consists of:

* Docker
* Apache
* PHP 8.1
* MediaWiki 1.39
* MySQL
* reproducible image
* deterministic builds

No runtime installation of extensions is allowed.

All extensions are installed during image build.

---

# 7. Docker Principles

The Docker image must contain:

* MediaWiki
* all extensions
* all skins
* PHP configuration
* Apache configuration
* entrypoint
* Composer dependencies

The container should be self-contained.

The only mounted directories should be persistent runtime data such as:

* images/
* optional LocalSettings overrides
* database volumes

No mounted source code.

---

# 8. Extension Policy

Every extension must satisfy one of the following.

## Standard Extension

Installed directly from upstream.

Version must be pinned.

Example:

* ParserFunctions
* Cite
* Scribunto
* TemplateData

---

## Fork

Installed from a maintained fork.

Repository and commit must be documented.

Current example:

SemanticResultFormats

---

## Custom

Stored inside this repository.

Current examples:

* Bootstrap
* Kma

---

# 9. Semantic MediaWiki Stack

Current production stack:

* SemanticMediaWiki
* Validator
* ParamProcessor
* Maps
* ModernTimeline
* SemanticResultFormats
* PageForms

The initialization order is important.

The migration documents the required load order inside LocalSettings.php.

---

# 10. Legacy Components

## Validator

Production uses the legacy Validator implementation.

Characteristics:

* no extension.json
* loaded through require_once()
* MediaWiki wrapper around ParamProcessor
* required by current SemanticResultFormats stack

This component must **not** be modernized during migration.

---

## SemanticResultFormats

Production uses a custom Knowledge-Wiki fork.

Characteristics:

* custom repository
* pinned commit
* namespace patch
* modified extension.json
* Composer installation intentionally disabled

Reason:

Composer attempts to install duplicate dependencies that already exist in the deployment.

---

## Kma

Kma is a custom fork of Chameleon.

Production depends on custom layout files.

Custom resources must remain unchanged.

---

## Bootstrap

Bootstrap is a project-specific extension.

It is maintained inside this repository.

---

# 11. Version Pinning

Floating versions are not allowed.

Every dependency must define:

* repository
* branch
* commit (when required)

This guarantees reproducible builds.

---

# 12. Documentation Policy

Every custom modification must be documented.

Future maintainers should never have to answer:

> "Why does this exist?"

Every non-standard component must explain:

* purpose
* origin
* compatibility constraints
* upgrade considerations

---

# 13. Migration Phases

## Phase 1

Project audit

Completed.

---

## Phase 2

Docker build refactoring

In Progress.

---

## Phase 3

Entrypoint redesign

Pending.

---

## Phase 4

LocalSettings refactoring

Pending.

---

## Phase 5

Documentation

Pending.

---

## Phase 6

Production validation

Pending.

---

# 14. Definition of Done

The migration is complete when the following conditions are satisfied.

* Docker image builds successfully
* MediaWiki starts successfully
* maintenance/update.php executes successfully
* Semantic MediaWiki initializes successfully
* SemanticResultFormats functions correctly
* Maps render correctly
* PageForms operate correctly
* custom Bootstrap extension loads
* Kma skin renders correctly
* uploads work
* search works
* no mounted extensions are required
* no mounted skins are required
* deployment is fully reproducible

---

# 15. Future Work

The current migration intentionally preserves behaviour.

Future work may include:

* upgrading to newer MediaWiki releases
* replacing legacy Validator
* replacing legacy ParamProcessor
* modernizing SemanticResultFormats
* removing historical compatibility patches
* adding CI/CD
* automated image publishing
* automated regression testing

These tasks are **explicitly outside the scope** of the current migration.
