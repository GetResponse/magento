<?php
namespace GetResponse\GetResponseIntegration\Block;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Webform
 * @package GetResponse\GetResponseIntegration\Block
 */
class Webform extends GetResponse
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Webform constructor.
     * @param Context $context
     * @param array $data
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Context $context, ObjectManagerInterface $objectManager)
    {
        parent::__construct($context, $objectManager);
        $this->_objectManager = $objectManager;
    }

    /**
     * @return mixed
     */
    public function getWebformSettings()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $webform_settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Webform');
        return $webform_settings->load($storeId, 'id_shop')->getData();
    }
}
