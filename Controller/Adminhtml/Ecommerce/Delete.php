<?php

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\GetresponseApiException;
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
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');

        if (empty($id)) {
            $this->messageManager->addErrorMessage(Message::INCORRECT_SHOP);
            $resultRedirect->setPath(self::BACK_URL);
            return $resultRedirect;
        }

        try {
            $service = new ShopService($this->repositoryFactory->createGetResponseApiClient());
            $service->deleteShop($id);
            $resultRedirect->setPath(self::BACK_URL);
            return $resultRedirect;
        } catch (GetresponseApiException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath(self::BACK_URL);
            return $resultRedirect;
        } catch (RepositoryException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath(self::BACK_URL);
            return $resultRedirect;
        } catch (ApiTypeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath(self::BACK_URL);
            return $resultRedirect;
        }
    }
}
