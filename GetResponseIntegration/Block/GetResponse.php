<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class GetResponse
 * @package GetResponse\GetResponseIntegration\Block
 */
class GetResponse extends Template
{
    /** @var ObjectManagerInterface */
    protected $_objectManager;

    /**
     * Export constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Context $context, ObjectManagerInterface $objectManager)
    {
        parent::__construct($context);
        $this->_objectManager = $objectManager;
    }

    /**
     * @return mixed
     */
    public function getStoreLanguage()
    {
        return $this->_scopeConfig->getValue('general/locale/code');
    }

    /**
     * @return mixed
     */
    public function getSettings()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        return $settings->load($storeId, 'id_shop')->getData();
    }

    /**
     * @return GetResponseAPI3
     */
    public function getClient()
    {

        $moduleInfo = $this->_objectManager->get('Magento\Framework\Module\ModuleList')->getOne('GetResponse_GetResponseIntegration');

        $version = isset($moduleInfo['setup_version']) ? $moduleInfo['setup_version'] : '';

        $settings = $this->getSettings();
        return new GetResponseAPI3($settings['api_key'], $settings['api_url'], $settings['api_domain'], $version);
    }
}
