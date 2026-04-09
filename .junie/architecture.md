---
name: magento-plugin-architecture
description: Overview of the architecture and directory structure of the GetResponse Integration for Magento 2.
---

# Architecture and Layers

The project uses a hybrid architecture, combining Magento 2 standards with elements of layered architecture:

- **API Layer (`Api/`)**: Responsible for outgoing communication to the GetResponse system.
  - `ApiService.php`: Main entry point for sending data (customers, orders, products).
  - `Api/Controller/`: Controllers handling incoming REST API requests (e.g., plugin configuration).
- **Domain Layer (`Domain/`)**: Contains pure business logic.
  - `Domain/Magento/`: Models representing configuration state in Magento (e.g., `LiveSynchronization`, `WebForm`).
  - `Domain/GetResponse/`: Models specific to the GetResponse data format.
  - `Domain/SharedKernel/`: Shared elements, like the `Scope` class (Store ID handling).
- **Presentation Layer (`Presenter/`)**: Transforms domain data into formats understood by the API/Views.
- **Observers (`Observer/`)**: React to Magento events and initiate data synchronization.
- **Magento Infrastructure**: Standard `etc/` (configuration), `view/` (views), `Block/` (view logic), `Helper/` folders.

# File Structure

- API Classes: `GetResponse\GetResponseIntegration\Api`
- Observers: `GetResponse\GetResponseIntegration\Observer`
- Domain Logic: `GetResponse\GetResponseIntegration\Domain`
- Admin Controllers: `GetResponse\GetResponseIntegration\Controller\Adminhtml`
