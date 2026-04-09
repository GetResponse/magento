---
name: magento-plugin-testing
description: Guidelines for unit testing and handling missing Magento references in the GetResponse Integration.
---

# Unit Tests

We place great emphasis on unit tests in the project.

- **Location**: Tests are located in `Test/Unit/`. The folder structure inside `Test/Unit/` should reflect the folder structure inside the main directory (e.g., the test for `Observer/MyObserver.php` should be in `Test/Unit/Observer/MyObserverTest.php`).
- **Base Class**: All unit tests should inherit from `GetResponse\GetResponseIntegration\Test\BaseTestCase`.
- **Mocking**: Use the helper method `$this->getMockWithoutConstructing(ClassName::class)` from `BaseTestCase` to create mocks without calling the constructor.
- **Convention**:
  - Namespace: `GetResponse\GetResponseIntegration\Test\Unit\...`
  - Every test must have `declare(strict_types=1);`.
  - Test methods should be public and have the `/** @test */` annotation.
  - Method naming: `should[ExpectedResult]When[Condition]`, e.g., `shouldSubscribeCustomerWhenLiveSynchronizationIsActive`.

- **Handling Missing Magento References**:
  - Magento often uses automatically generated classes (e.g., `*ExtensionInterface`, `*Factory`).
  - If a unit test requires such a class but it is not available in the `vendor/` directory during testing, add a stub in `Test/MissingReferences/`.
  - Stubs in `Test/MissingReferences/` are automatically loaded via `composer.json`'s `autoload-dev` section.
  - See `Test/MissingReferences/Magento/Catalog/Api/Data/ProductExtensionInterface.php` for an example of a stub interface.
