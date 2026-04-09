---
name: magento-plugin-coding-standards
description: Coding conventions, standards, and practices for the GetResponse Integration for Magento 2.
---

# Coding Conventions

- **Standards**: PHP 7.2+, PSR-4, Magento Coding Standard.
- **Strict Types**: Every PHP file MUST start with `declare(strict_types=1);`.
- **Dependency Injection**: Always use the constructor (Dependency Injection). Avoid direct use of `ObjectManager` (exception: legacy controllers, if necessary).
- **Typing**: Use parameter and return types wherever possible in PHP 7.2.
- **Domain Models**: Often have static factory methods like `createFromRequest` or `createFromRepository`.
