<?php
namespace GetResponse\GetResponseIntegration\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Helper\Config;

/**
 * Class Ecommerce
 * @package GetResponse\GetResponseIntegration\Block
 */
class Ecommerce extends GetResponse
{
    /** @var ObjectManagerInterface */
    protected $_objectManager;

    /** @var ScopeConfigInterface  */
    private $scopeConfig;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Context $context, ObjectManagerInterface $objectManager)
    {
        parent::__construct($context, $objectManager);

        $this->_objectManager = $objectManager;
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * @return string
     */
    public function getShopStatusFromConfig()
    {
        $shopStatus = $this->scopeConfig->getValue(Config::SHOP_STATUS);

        if ('enabled' === $shopStatus) {
            return 'enabled';
        }

        return 'disabled';
    }

    /**
     * @return string
     */
    public function getCurrentShopId()
    {
        return $this->scopeConfig->getValue(Config::SHOP_ID);
    }

    /**
     * @return array
     */
    public function getShops()
    {
        return (array) $this->getClient()->getShops();
    }
}
