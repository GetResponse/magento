<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Route;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\Shop\ShopsCollection;
use GrShareCode\Shop\ShopService;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;

class Export extends AdminTemplate
{
    use CustomFieldsTrait;
    use AutoresponderTrait;

    private $serializer;
    private $repository;

    public function __construct(
        Context $context,
        ApiClientFactory $apiClientFactory,
        CustomFieldService $customFieldService,
        CustomFieldsMappingService $customFieldsMappingService,
        MagentoStore $magentoStore,
        SerializerInterface $serializer,
        Repository $repository
    ) {
        parent::__construct($context, $magentoStore);

        $this->customFieldService = $customFieldService;
        $this->customFieldsMappingService = $customFieldsMappingService;
        $this->serializer = $serializer;
        $this->repository = $repository;
        $this->routePrefix = Route::EXPORT_INDEX_ROUTE;

        $this->apiClient = $apiClientFactory->createGetResponseApiClient($this->getScope());
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
}
