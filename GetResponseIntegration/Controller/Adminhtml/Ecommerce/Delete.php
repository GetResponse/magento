<?php

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Delete
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce
 */
class Delete extends Action
{
    /** @var PageFactory */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
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
            $resultRedirect->setPath('getresponseintegration/ecommerce/index');
            return $resultRedirect;
        }

        $block = $this->_objectManager->create('GetResponse\GetResponseIntegration\Block\Ecommerce');

        $client = $block->getClient();
        $response = $client->deleteShop($id);

        if (isset($response->httpStatus) && $response->httpStatus > 204) {
            $this->messageManager->addErrorMessage($response->codeDescription . ' - uuid: ' . $response->uuid);
        } else {
            $this->messageManager->addSuccessMessage('Store removed');
        }

        $resultRedirect->setPath('getresponseintegration/ecommerce/index');
        return $resultRedirect;
    }
}