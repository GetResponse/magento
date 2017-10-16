<?php

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;

/**
 * Class Delete
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce
 */
class Delete extends Action
{
    /** @var GrRepository */
    private $grRepository;

    const BACK_URL = 'getresponseintegration/ecommerce/index';

    /**
     * @param Context $context
     * @param GrRepository $grRepository
     */
    public function __construct(Context $context, GrRepository $grRepository)
    {
        parent::__construct($context);
        $this->grRepository = $grRepository;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id');

        if (empty($id)) {
            $this->messageManager->addErrorMessage('Incorrect shop');
            $resultRedirect->setPath(self::BACK_URL);
            return $resultRedirect;
        }

        $response = $this->grRepository->deleteShop($id);

        if (isset($response->httpStatus) && $response->httpStatus > 204) {
            $this->messageManager->addErrorMessage($response->codeDescription . ' - uuid: ' . $response->uuid);
        } else {
            $this->messageManager->addSuccessMessage('Store removed');
        }

        $resultRedirect->setPath(self::BACK_URL);
        return $resultRedirect;
    }
}
