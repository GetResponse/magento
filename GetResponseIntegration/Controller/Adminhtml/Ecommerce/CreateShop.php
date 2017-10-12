<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use GetResponse\GetResponseIntegration\Block\Ecommerce;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\Http;

/**
 * Class CreateShop
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class CreateShop extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * CreateCampaign constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     */
    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        /** @var Ecommerce $block */
        $block = $this->_objectManager->create('GetResponse\GetResponseIntegration\Block\Ecommerce');
        $lang = substr($block->getStoreLanguage(), 0, 2);
        $currency = 'EUR';

        if (!isset($data['shop_name']) || strlen($data['shop_name']) === 0) {
            die(json_encode(['error' => 'Incorrect shop name']));
        }

        echo json_encode($block->getClient()->createShop($data['shop_name'], $lang, $currency));
        die;
    }
}