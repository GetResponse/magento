<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Ecommerce\ReadModel\EcommerceReadModel;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Route;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\Shop\ShopsCollection;
use GrShareCode\Shop\ShopService;
use Magento\Framework\View\Element\Template\Context;

class Ecommerce extends AdminTemplate
{
    private $ecommerceReadModel;

    public function __construct(
        Context $context,
        ApiClientFactory $apiClientFactory,
        MagentoStore $magentoStore,
        EcommerceReadModel $ecommerceReadModel
    ) {
        parent::__construct($context, $magentoStore);

        $this->ecommerceReadModel = $ecommerceReadModel;
        $this->routePrefix = Route::ECOMMERCE_INDEX_ROUTE;
        $this->apiClient =  $apiClientFactory->createGetResponseApiClient($this->getScope());
    }

    public function getShopStatus(): string
    {
        return $this->ecommerceReadModel->getShopStatus($this->getScope());
    }

    public function getCurrentShopId()
    {
        return $this->ecommerceReadModel->getShopId($this->getScope());
    }

    public function getEcommerceListId()
    {
        return $this->ecommerceReadModel->getListId($this->getScope());
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
