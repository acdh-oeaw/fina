# FINA Technical Specification

## Purpose

This document defines the technical constraints, compatibility requirements and implementation rules for the FINA MediaWiki deployment.

It is intended for developers maintaining the application and should be considered the primary engineering reference for the project.

---

# Scope

The objective of the current migration is to reproduce the existing production deployment using a deterministic Docker build.

The migration is **not** intended to modernize the application.

Behavior compatibility has priority over code modernization.

---

# Platform

| Component          | Version  |
| ------------------ | -------- |
| MediaWiki          | 1.39 LTS |
| PHP                | 8.1      |
| Apache             | 2.4      |
| MySQL              | 8.x      |
| Semantic MediaWiki | 4.2      |

---

# Deployment Model

The application is deployed as a single Docker image.

The image contains:

* MediaWiki
* all required extensions
* all required skins
* Composer dependencies
* Apache configuration
* PHP configuration

No application code is mounted at runtime.

---

# Runtime Data

Persistent runtime data consists only of:

* uploaded files
* database

Application code must always be part of the image.

---

# Extension Categories

Every extension belongs to one of the following categories.

## Core Extensions

Maintained by the MediaWiki project.

Installed directly from upstream.

Examples:

* ParserFunctions
* Scribunto
* Cite
* CategoryTree
* TemplateData
* WikiEditor
* TemplateStyles

---

## Third-Party Extensions

Maintained by external projects.

Examples:

* SemanticMediaWiki
* Maps
* ModernTimeline
* PageForms
* Widgets

---

## Custom Extensions

Maintained by the FINA project.

Examples:

* Bootstrap
* Kma

---

# Semantic Stack

The production semantic stack consists of:

1. SemanticMediaWiki
2. Validator
3. ParamProcessor
4. Maps
5. ModernTimeline
6. SemanticResultFormats
7. PageForms
8. Widgets

The initialization order is considered part of the production deployment and should not be changed without understanding dependency implications.

---

# SemanticResultFormats

Production uses a custom fork.

Repository:

Knowledge-Wiki / SemanticResultFormats

Characteristics:

* custom commit
* modified extension.json
* manual namespace registration
* legacy compatibility patches

The SRF namespace is manually registered.

The extension intentionally does not execute Composer during image build because dependency installation conflicts with dependencies already provided by the rest of the Semantic stack.

---

# Validator

Production uses the legacy Validator implementation.

Characteristics:

* legacy MediaWiki wrapper
* loaded through require_once()
* no extension.json
* required by Maps and SemanticResultFormats compatibility

This behavior is intentional.

Do not replace it with wfLoadExtension() without validating the entire Semantic stack.

---

# Maps

Maps depends on:

* Validator
* ParamProcessor
* ModernTimeline

Compatibility with the production deployment depends on preserving this dependency chain.

---

# Custom Skin

The project uses a custom skin named Kma.

Kma is based on Chameleon and provides:

* custom layouts
* project branding
* SCSS customization
* navigation

Production layout files must remain unchanged during the migration.

---

# Composer Policy

Composer is executed only where required.

Current Composer targets:

* repository root
* SemanticMediaWiki
* Maps
* TemplateStyles
* Bootstrap

Composer must not be executed inside SemanticResultFormats.

---

# Docker Rules

The repository maintains a single Dockerfile.

The Docker image must be fully reproducible.

Every external dependency must specify an explicit branch or commit.

Floating versions should be avoided.

---

# LocalSettings Policy

LocalSettings.php should be organized into clearly separated sections.

Recommended order:

1. Core configuration
2. Database
3. Uploads
4. Mail
5. Performance
6. Search
7. Core extensions
8. Semantic stack
9. Custom extensions
10. Skins
11. Namespaces
12. Permissions
13. Debugging

The load order of Semantic extensions is considered part of the deployment specification.

---

# Legacy Compatibility

The migration intentionally preserves several historical compatibility workarounds.

Known examples include:

* legacy Validator loading
* SemanticResultFormats namespace patch
* modified extension.json
* disabled Composer inside SemanticResultFormats
* Chameleon/Kma compatibility symlinks

These workarounds should not be removed unless the entire stack is upgraded and fully validated.

---

# Coding Rules

When modifying this repository:

* preserve production behavior
* prefer compatibility over modernization
* document every workaround
* keep commits small
* pin dependency versions
* avoid implicit behavior

---

# Testing Requirements

Every significant change must verify:

* Docker image builds successfully
* MediaWiki starts correctly
* maintenance/update.php completes successfully
* SemanticMediaWiki initializes
* SemanticResultFormats initializes
* Maps loads correctly
* PageForms work
* Widgets render correctly
* Kma skin loads
* uploads function correctly

---

# Out of Scope

The following tasks are explicitly excluded from the current migration:

* upgrading MediaWiki
* upgrading PHP
* replacing SemanticResultFormats
* replacing Validator
* redesigning custom extensions
* redesigning the Kma skin

These activities belong to future modernization efforts after a stable Docker deployment has been achieved.
