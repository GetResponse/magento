<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use Exception;
use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Store\ReadModel\StoreReadModel;
use GetResponse\GetResponseIntegration\Helper\Message;
use GrShareCode\Shop\Command\AddShopCommand;
use GrShareCode\Shop\ShopService;
use Magento\Backend\App\Action\Context;

class CreateShop extends AbstractController
{
    private $apiClientFactory;
    private $storeReadModel;

    public function __construct(
        Context $context,
        ApiClientFactory $apiClientFactory,
        StoreReadModel $storeReadModel
    ) {
        parent::__construct($context);
        $this->apiClientFactory = $apiClientFactory;
        $this->storeReadModel = $storeReadModel;
    }

    public function execute()
    {
        parent::execute();

        $data = $this->request->getPostValue();

        if (!isset($data['shop_name']) || $data['shop_name'] === '') {
            return $this->renderJson(['error' => Message::INCORRECT_SHOP_NAME]);
        }

        try {
            $apiClient = $this->apiClientFactory->createGetResponseApiClient($this->scope);

            $service = new ShopService($apiClient);
            $shopId = $service->addShop(
                new AddShopCommand(
                    $data['shop_name'],
                    $this->storeReadModel->getStoreLanguage($this->scope),
                    $this->storeReadModel->getStoreCurrency($this->scope)
                )
            );

            return $this->renderJson(['shopId' => $shopId, 'name' => $data['shop_name']]);
        } catch (Exception $e) {
            return $this->renderJson(['error' => $e->getMessage()]);
        }
    }
}
