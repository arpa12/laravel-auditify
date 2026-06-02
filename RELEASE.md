# Release & Semantic Versioning Guide

This document defines the release lifecycle, versioning guidelines, and deployment workflows for the **`arpanihan/auditify`** package.

---

## 1. Semantic Versioning (SemVer) Strategy

Auditify strictly follows [Semantic Versioning 2.0.0 (SemVer)](https://semver.org/). Version numbers follow the format `MAJOR.MINOR.PATCH` (e.g., `v1.2.4`):

### MAJOR Version (`X.0.0`)
Incremented when introducing **breaking API changes** that require developer intervention or code modifications in the host application.
* *Examples*:
  * Renaming traits (like `Auditable`).
  * Changing core database schema fields without migrations or wrappers.
  * Removing deprecated public methods in `AuditifyService`.
  * Dropping support for older PHP versions (e.g. dropping PHP 8.2).

### MINOR Version (`1.Y.0`)
Incremented when adding **new backward-compatible features**.
* *Examples*:
  * Adding a new threat checking rule engine.
  * Introducing a new chart visual layout or navigation link to the dashboard views.
  * Adding helper methods to bypass auditing or specify explicit models.

### PATCH Version (`1.0.Z`)
Incremented when executing **backward-compatible bug fixes**.
* *Examples*:
  * Resolving minor layout alignments or CSS padding fixes on mobile screens.
  * Correcting syntax warnings or type hint mismatches.
  * Adjusting default config thresholds defaults.

---

## 2. Compatibility Matrix

To guarantee package stability, every release must be tested against the following PHP and Laravel matrix via testing orchestrations (`orchestra/testbench`):

| Package Version | PHP Version | Laravel Version | Support Status |
|---|---|---|---|
| `v1.0.x` | `^8.2 \| ^8.3` | `^10.0 \| ^11.0` | Active (Bug fixes & Security) |

---

## 3. Tagging & Publication Workflow

Packagist automatically detects releases using Git tags pushed to the package repository. 

Follow this checklist to publish a new tag:

### Step 1: Pre-Release Verification
Run automated tests locally to verify that all code compiles, lint issues are resolved, and tests pass:
```bash
composer validate --strict
vendor/bin/phpunit
```

### Step 2: Update Changelog
Document all additions, modifications, and fixes under the appropriate version header inside `CHANGELOG.md` following [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) formats.

### Step 3: Git Tagging
Commit your changes and tag the commit with the target version number:
```bash
# Commit documentation and version edits
git add .
git commit -m "chore: prepare release v1.0.0"

# Create signed git tag
git tag -a v1.0.0 -m "Release version 1.0.0"

# Push tag to GitHub origin
git push origin main --tags
```

### Step 4: Packagist Sync
Verify that the package is synced successfully on [Packagist.org](https://packagist.org/). If the repository has the Packagist GitHub Webhook configured, the new version will appear instantly. Otherwise, click the **Update** button on the Packagist package dashboard.

---

## 4. Maintenance and Patching Policy

- **Security Patches**: Critical security issues (like XSS bypasses or unauthorized route escalation) will be backported and released as immediate patches to the latest active major/minor branches.
- **PHP Mappings**: As PHP versions reach End of Life (EOL), they will be phased out in subsequent minor/major releases.
