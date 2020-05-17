<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\Magento\Store\ReadModel\StoreReadModel;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Message;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Shop\Command\AddShopCommand;
use GrShareCode\Shop\ShopService;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;

class CreateShop extends AbstractController
{
    private $apiClientFactory;
    private $resultJsonFactory;
    private $magentoStore;
    private $storeReadModel;

    public function __construct(
        Context $context,
        ApiClientFactory $apiClientFactory,
        JsonFactory $resultJsonFactory,
        MagentoStore $magentoStore,
        StoreReadModel $storeReadModel
    ) {
        parent::__construct($context);
        $this->apiClientFactory = $apiClientFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->magentoStore = $magentoStore;
        $this->storeReadModel = $storeReadModel;
    }

    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        $scope = new Scope($this->magentoStore->getStoreIdFromUrl());

        if (!isset($data['shop_name']) || $data['shop_name'] === '') {
            return $this->resultJsonFactory->create()->setData(['error' => Message::INCORRECT_SHOP_NAME]);
        }

        try {
            $apiClient = $this->apiClientFactory->createGetResponseApiClient($scope);

            $service = new ShopService($apiClient);
            $shopId = $service->addShop(
                new AddShopCommand(
                    $data['shop_name'],
                    $this->storeReadModel->getStoreLanguage($scope),
                    $this->storeReadModel->getStoreCurrency($scope)
                )
            );

            return $this->resultJsonFactory->create()->setData(
                [
                    'shopId' => $shopId,
                    'name' => $data['shop_name']
                ]
            );
        } catch (GetresponseApiException $e) {
            return $this->resultJsonFactory->create()->setData(['error' => $e->getMessage()]);
        } catch (ApiException $e) {
            return $this->resultJsonFactory->create()->setData(['error' => $e->getMessage()]);
        }
    }
}
