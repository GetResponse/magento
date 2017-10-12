<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Registration
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class Registration extends Action
{
    protected $resultPageFactory;

    /**
     * Registration constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $block = $this->_objectManager->create('GetResponse\GetResponseIntegration\Block\Settings');
        $checkApiKey = $block->checkApiKey();
        if ($checkApiKey === false) {
            $this->messageManager->addWarningMessage('Your API key is not valid! Please update your settings.');
        } elseif ($checkApiKey === 0) {
            $this->messageManager->addWarningMessage('Your API key is empty. In order to use this function you need to save your API key');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
        $resultPage->getConfig()->getTitle()->prepend('Add Contacts During Registrations');

        return $resultPage;
    }

}