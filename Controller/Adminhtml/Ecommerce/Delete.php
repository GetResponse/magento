<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use Exception;
use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Helper\Route;
use GrShareCode\Shop\Command\DeleteShopCommand;
use GrShareCode\Shop\ShopService;
use Magento\Backend\App\Action\Context;

class Delete extends AbstractController
{
    private $apiClient;

    public function __construct(
        Context $context,
        ApiClientFactory $apiClientFactory,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context);

        $this->apiClient = $apiClientFactory->createGetResponseApiClient(
            new Scope($magentoStore->getStoreIdFromUrl())
        );
    }

    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id');

            if (empty($id)) {
                throw new Exception(Message::INCORRECT_SHOP);
            }

            $service = new ShopService($this->apiClient);
            $service->deleteShop(new DeleteShopCommand($id));

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath(Route::ECOMMERCE_INDEX_ROUTE);

            return $resultRedirect;
        } catch (Exception $e) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath(Route::ECOMMERCE_INDEX_ROUTE);
            return $resultRedirect;
        }
    }
}
