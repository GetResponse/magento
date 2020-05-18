<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use Exception;
use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Exception\IdNotFoundException;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Helper\Route;
use GrShareCode\Shop\Command\DeleteShopCommand;
use GrShareCode\Shop\ShopService;
use Magento\Backend\App\Action\Context;

class Delete extends AbstractController
{
    private $apiClient;

    public function __construct(Context $context, ApiClientFactory $apiClientFactory)
    {
        parent::__construct($context);

        $this->apiClient = $apiClientFactory->createGetResponseApiClient($this->scope);
    }

    public function execute()
    {
        parent::execute();

        if (!$this->isConnected()) {
            return $this->redirectToStore(Route::ACCOUNT_INDEX_ROUTE);
        }

        try {
            $id = $this->request->getParam('id');

            if (empty($id)) {
                throw new IdNotFoundException(Message::INCORRECT_SHOP);
            }

            $service = new ShopService($this->apiClient);
            $service->deleteShop(new DeleteShopCommand($id));

            return $this->redirect($this->_redirect->getRefererUrl(), Message::SHOP_DELETED);
        } catch (Exception $e) {
            return $this->redirect($this->_redirect->getRefererUrl(),$e->getMessage(), true);
        }
    }
}
