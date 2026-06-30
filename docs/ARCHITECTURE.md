# FINA System Architecture

## Overview

FINA is a customized MediaWiki deployment built around Semantic MediaWiki and several project-specific extensions and skins.

The application is deployed as a single Docker container running Apache and PHP.

The deployment philosophy is to build a fully reproducible application image where all application code is embedded during the image build.

---

# High-Level Architecture

```text
                    +---------------------------+
                    |         Browser          |
                    +-------------+------------+
                                  |
                              HTTP/HTTPS
                                  |
                    +-------------v------------+
                    |      Apache HTTPD       |
                    +-------------+------------+
                                  |
                               PHP 8.1
                                  |
                    +-------------v------------+
                    |       MediaWiki         |
                    +-------------+------------+
                                  |
               +------------------+------------------+
               |                                     |
      Semantic MediaWiki                    Core Extensions
               |                                     |
     Semantic Extension Stack                 Parser / UI
               |
        Custom Extensions
               |
        Custom Skin (Kma)
```

---

# Runtime Components

The production deployment consists of the following runtime services.

## Web Server

Apache HTTP Server

Responsibilities:

* request routing
* URL rewriting
* static assets
* PHP execution

---

## PHP

PHP 8.1

Responsibilities:

* MediaWiki execution
* Semantic MediaWiki
* custom extensions
* parser
* maintenance scripts

---

## Database

MySQL

Responsibilities:

* MediaWiki data
* Semantic data
* user accounts
* page revisions
* uploaded metadata

---

## Search

OpenSearch

Responsibilities:

* full text indexing
* semantic search integration
* CirrusSearch backend

---

# MediaWiki Layer

MediaWiki provides:

* authentication
* page rendering
* parser
* file management
* extension framework
* skin framework

No MediaWiki core modifications are expected.

---

# Semantic Layer

The semantic layer extends MediaWiki with structured data.

Current components include:

* SemanticMediaWiki
* Validator
* ParamProcessor
* Maps
* ModernTimeline
* SemanticResultFormats
* PageForms
* Widgets

Several of these components contain legacy compatibility requirements inherited from the production deployment.

---

# Custom Components

The project includes custom software maintained inside this repository.

## Bootstrap

Project-specific MediaWiki extension.

Purpose:

* application-specific functionality

---

## Kma

Project-specific skin based on Chameleon.

Provides:

* layout
* SCSS customization
* navigation
* branding
* custom components

---

# Deployment Model

The application is deployed as a Docker image.

The image contains:

* MediaWiki
* extensions
* skins
* Composer dependencies
* Apache configuration
* PHP configuration

The image must be self-contained.

No application source code is mounted at runtime.

---

# Persistent Data

The following data must remain persistent outside the container.

* uploaded files
* MySQL database

Everything else belongs inside the image.

---

# Design Principles

The architecture follows the following principles.

* reproducible builds
* immutable application image
* deterministic dependency versions
* production compatibility
* documented customizations
* minimal runtime configuration

---

# Future Evolution

The current migration intentionally preserves the existing architecture.

Future work may include:

* MediaWiki upgrades
* PHP upgrades
* dependency modernization
* CI/CD improvements
* automated testing

These activities are explicitly outside the scope of the current migration.
