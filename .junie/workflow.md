---
name: magento-plugin-workflow
description: Environment setup using Docker and Quality Assurance (QA) workflow using the Makefile.
---

# Development and QA Workflow

The project uses a Docker-based environment for consistency across different PHP versions (7.2 and 8.3).

### Environment Setup
- **PHP 7.2**: `make setup-php72` (starts containers, installs deps, syncs vendor).
- **PHP 8.3**: `make setup-php83` (starts containers, installs deps, syncs vendor).
- **Cleanup**: `make clean` (stops containers) or `make clean-deps` (removes vendor and lock files).

### Quality Assurance (QA)
Always run these commands before submitting changes:
- **Unit Tests**:
  - PHP 7.2: `make php72-test` (uses `Test/phpunit8.xml`)
  - PHP 8.3: `make php83-test` (uses `Test/phpunit9.xml`)
  - All versions: `make test-all`
- **Static Analysis**: `make phpstan` (runs PHPStan on PHP 7.2 environment).
- **Coding Standards**:
  - Check: `make phpcs` (runs Magento Coding Standard).
  - Auto-fix: `make cs-fixer` (runs PHP-CS-Fixer).

### Composer Scripts
The `Makefile` targets often wrap `composer.json` scripts. You can also run them directly inside the container if needed:
- `composer test:php72` / `composer test:php83`
- `composer phpstan`
- `composer phpcs`
- `composer cs-fix`
