---
name: magento-plugin-examples
description: Example implementations of observers, unit tests, domain models, and controllers in the project.
---

# Example Implementations

Use these files as patterns when implementing new features:

- **Observer**: See `Observer/CustomerRegisterSuccess.php` as a pattern for event handling.
- **Unit Test**: See `Test/Unit/Observer/CustomerRegisterSuccessTest.php` as a pattern for creating tests with mocks.
- **Domain Model**: See `Domain/Magento/LiveSynchronization.php` for a Value Object pattern with validation.
- **API Controller**: See `Api/Controller/ConfigurationController.php` for a REST API handling pattern.
