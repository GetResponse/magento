<?php

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\Store;
use Magento\Framework\App\Request\Http;

/**
 * Class SaveShop
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce
 */
class SaveShop extends Action
{
    /** @var PageFactory */
    private $resultPageFactory;

    /** @var ConfigInterface  */
    private $resourceConfig;

    private $cache;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ConfigInterface $resourceConfig
     * @param TypeListInterface $cache
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, ConfigInterface $resourceConfig, TypeListInterface $cache)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resourceConfig = $resourceConfig;
        $this->cache = $cache;
    }

    /**
     * @return Redirect|Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
        $resultPage->getConfig()->getTitle()->prepend('GetResponse Ecommerce');

        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        if (isset($data['e_commerce_status']) && '1' === $data['e_commerce_status']) {

            if (empty($data['shop_id'])) {
                $this->messageManager->addErrorMessage('You need to choose a store');
                $resultRedirect->setPath('getresponseintegration/ecommerce/index');
                return $resultRedirect;
            }

            $this->resourceConfig->saveConfig(
                Config::SHOP_STATUS,
                (isset($data['e_commerce_status']) && '1' === $data['e_commerce_status']) ? 'enabled' : 'disabled',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            );

            $this->resourceConfig->saveConfig(
                Config::SHOP_ID,
                $data['shop_id'],
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            );
        } else {

            $this->resourceConfig->saveConfig(
                Config::SHOP_STATUS,
                'disabled',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            );

            $this->resourceConfig->saveConfig(
                Config::SHOP_ID,
                null,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            );
        }

        $this->cache->cleanType('config');
        $this->messageManager->addSuccessMessage('Ecommerce settings saved');

        $resultRedirect->setPath('getresponseintegration/ecommerce/index');
        return $resultRedirect;
    }
}