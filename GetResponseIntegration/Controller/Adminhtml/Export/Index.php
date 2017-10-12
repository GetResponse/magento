<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Export;

use GetResponse\GetResponseIntegration\Block\Export;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Export
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    public $grApi;

    /**
     * Index constructor.
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
        /** @var Export $block */
        $block = $this->_objectManager->create('GetResponse\GetResponseIntegration\Block\Export');
        $checkApiKey = $block->checkApiKey();
        if ($checkApiKey === false) {
            $this->messageManager->addWarningMessage('Your API key is not valid! Please update your settings.');
        } elseif ($checkApiKey === 0) {
            $this->messageManager->addWarningMessage('Your API key is empty. In order to use this function you need to save your API key');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::export');
        $resultPage->getConfig()->getTitle()->prepend('Export Customer Data on Demand');

        return $resultPage;
    }
}