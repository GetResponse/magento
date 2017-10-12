<?php
namespace GetResponse\GetResponseIntegration\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class Header
 * @package GetResponse\GetResponseIntegration\Block
 */
class Header extends Template
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager)
    {
        parent::__construct($context);
        $this->_objectManager = $objectManager;
    }

    /**
     * @return string
     */
    public function getSnippetCode()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        $data = $settings->load($storeId, 'id_shop')->getData();

        if (isset($data['web_traffic']) && isset($data['tracking_code_snippet']) && 'disabled' !== $data['web_traffic']) {
            return $data['tracking_code_snippet'];
        }
    }
}
