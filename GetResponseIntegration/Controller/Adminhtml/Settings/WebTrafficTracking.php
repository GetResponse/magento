<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Page\Interceptor;

/**
 * Class WebTrafficTracking
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class WebTrafficTracking extends Action
{
    /** @var PageFactory */
    protected $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return Interceptor
     */
    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        if (isset($data['updateWebTraffic'])) {
            $this->updateWebTraffic($data);
            $message = (isset($data['web_traffic']) && '1' === $data['web_traffic']) ? 'Web event traffic tracking enabled' : 'Web event traffic tracking disabled';
            $this->messageManager->addSuccessMessage($message);
        }

        $block = $this->_objectManager->create('GetResponse\GetResponseIntegration\Block\Settings');
        $checkApiKey = $block->checkApiKey();
        if (false === $checkApiKey) {
            $this->messageManager->addWarningMessage('Your API key is not valid! Please update your settings.');
        } elseif ($checkApiKey === 0) {
            $this->messageManager->addWarningMessage('Your API key is empty. In order to use this function you need to save your API key');
        }

        /** @var Interceptor $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
        $resultPage->getConfig()->getTitle()->prepend('Web Event Tracking');

        return $resultPage;
    }

    /**
     * @param array $data
     */
    private function updateWebTraffic($data)
    {
        $status = (isset($data['web_traffic']) && '1' === $data['web_traffic']) ? 'enabled' : 'disabled';

        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');

        $settings->load($storeId, 'id_shop')
            ->setWebTraffic($status)
            ->save();
    }
}
