<?php

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use Magento\Backend\App\Action\Context;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class Delete
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce
 */
class Delete extends AbstractController
{
    const BACK_URL = 'getresponse/ecommerce/index';

    /** @var GrRepository */
    private $grRepository;

    /**
     * @param Context $context
     * @param RepositoryFactory $repositoryFactory
     * @param RepositoryValidator $repositoryValidator
     * @throws RepositoryException
     */
    public function __construct(
        Context $context,
        RepositoryFactory $repositoryFactory,
        RepositoryValidator $repositoryValidator
    ) {
        parent::__construct($context, $repositoryValidator);
        $this->grRepository = $repositoryFactory->createRepository();

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

        $response = $this->grRepository->deleteShop($id);

        if (isset($response->httpStatus) && $response->httpStatus > 204) {
            $this->messageManager->addErrorMessage(Message::DELETE_SHOP_ERROR . ' - uuid: ' . $response->uuid);
        } else {
            $this->messageManager->addSuccessMessage(Message::STORE_REMOVED);
        }

        $resultRedirect->setPath(self::BACK_URL);

        return $resultRedirect;
    }
}
