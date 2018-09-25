<?php

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use Exception;
use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GrShareCode\Shop\ShopService;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class Delete
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce
 */
class Delete extends AbstractController
{
    const BACK_URL = 'getresponse/ecommerce/index';

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /**
     * @param Context $context
     * @param RepositoryFactory $repositoryFactory
     * @param RepositoryValidator $repositoryValidator
     */
    public function __construct(
        Context $context,
        RepositoryFactory $repositoryFactory,
        RepositoryValidator $repositoryValidator
    ) {
        parent::__construct($context, $repositoryValidator);
        $this->repositoryFactory = $repositoryFactory;
        return $this->checkGetResponseConnection();
    }

    /**
     * @return ResponseInterface|Redirect
     */
    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id');

            if (empty($id)) {
                throw new Exception(Message::INCORRECT_SHOP);
            }

            $service = new ShopService($this->repositoryFactory->createGetResponseApiClient());
            $service->deleteShop($id);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath(self::BACK_URL);
            return $resultRedirect;
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @param Exception $e
     * @return Redirect
     */
    private function handleException(Exception $e)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->messageManager->addErrorMessage($e->getMessage());
        $resultRedirect->setPath(self::BACK_URL);
        return $resultRedirect;
    }
}
