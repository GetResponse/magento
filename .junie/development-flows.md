---
name: magento-plugin-development-flows
description: Common business logic flows such as adding new synchronization events and configuration management.
---

# Key Flows

### Adding a new synchronization (e.g., new event)
1. Check if an appropriate sending method exists in `Api/ApiService.php`. If not - add it.
2. Define the event in `etc/events.xml` or `etc/adminhtml/events.xml`.
3. Create an Observer class in `Observer/`.
4. In the Observer, check the `LiveSynchronization` configuration (whether the synchronization is enabled for the given Scope).

### Configuration management
- Configuration is saved in the `core_config_data` table via `Domain\Magento\Repository`.
- Access to configuration is through domain models (e.g., `LiveSynchronization::createFromRepository($repository->getLiveSynchronization($scopeId))`).
