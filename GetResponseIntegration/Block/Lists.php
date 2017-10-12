<?php
namespace GetResponse\GetResponseIntegration\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Helper\Config;

/**
 * Class Lists
 * @package GetResponse\GetResponseIntegration\Block
 */
class Lists extends GetResponse
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
     * @return mixed
     */
    public function getAccountFromFields()
    {
        return $this->getClient()->getAccountFromFields();
    }

    /**
     * @param $lang
     * @return mixed
     */
    public function getSubscriptionConfirmationsSubject($lang)
    {
        return $this->getClient()->getSubscriptionConfirmationsSubject($lang);
    }

    /**
     * @param $lang
     * @return mixed
     */
    public function getSubscriptionConfirmationsBody($lang)
    {
        return $this->getClient()->getSubscriptionConfirmationsBody($lang);
    }

    public function getBackUrl()
    {
        return $this->createBackUrl($this->getRequest()->getParam('back'));
    }

    /**
     * @param string $back
     *
     * @return string
     */
    private function createBackUrl($back)
    {
        switch($back) {
            case 'export':
                return 'getresponseintegration/export/index';
                break;

            case 'registration':
                return 'getresponseintegration/settings/registration';
                break;
        }

        return '';
    }
}
