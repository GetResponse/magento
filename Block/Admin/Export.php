<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeCollection;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Route;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\Shop\ShopsCollection;
use GrShareCode\Shop\ShopService;
use Magento\Framework\View\Element\Template\Context;

class Export extends AdminTemplate
{
    private $customFieldService;
    private $customFieldsMappingService;
    private $repository;

    public function __construct(
        Context $context,
        ApiClientFactory $apiClientFactory,
        Repository $repository,
        CustomFieldService $customFieldService,
        CustomFieldsMappingService $customFieldsMappingService,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context, $magentoStore);

        $this->customFieldService = $customFieldService;
        $this->customFieldsMappingService = $customFieldsMappingService;
        $this->repository = $repository;

        $this->apiClient = $apiClientFactory->createGetResponseApiClient($this->getScope());
    }

    /**
     * @return CustomFieldsMappingCollection
     */
    public function getCustomFieldsMapping(): CustomFieldsMappingCollection
    {
        return CustomFieldsMappingCollection::createFromRepository(
            $this->repository->getCustomFieldsMappingForRegistration(
                $this->getScope()->getScopeId()
            )
        );
    }

    /**
     * @return ShopsCollection
     * @throws GetresponseApiException
     */
    public function getShops(): ShopsCollection
    {
        return (new ShopService($this->apiClient))->getAllShops();
    }

    /**
     * @return ContactListCollection
     * @throws GetresponseApiException
     */
    public function getLists(): ContactListCollection
    {
        return (new ContactListService($this->apiClient))->getAllContactLists();
    }

    /**
     * @return array
     * @throws ApiException
     * @throws GetresponseApiException
     */
    public function getCustomFieldsFromGetResponse(): array
    {
        $result = [];

        $customFields = $this->customFieldService->getCustomFields($this->getScope());

        foreach ($customFields as $customField) {
            $result[] = [
                'id' => $customField->getId(),
                'name' => $customField->getName(),
            ];
        }

        return $result;
    }

    public function getMagentoCustomerAttributes(): MagentoCustomerAttributeCollection
    {
        return $this->customFieldsMappingService->getMagentoCustomerAttributes();
    }

    public function getPageUrlForScope(int $scope): string
    {
        return $this->getUrl(Route::EXPORT_INDEX_ROUTE . '/' . Config::SCOPE_TAG . '/' . $scope);
    }
}
