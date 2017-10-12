<?php
namespace GetResponse\GetResponseIntegration\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Helper\Config;

/**
 * Class Rules
 * @package GetResponse\GetResponseIntegration\Block
 */
class Rules extends GetResponse
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
    public function getStoreCategories()
    {
        $_categoryHelper = $this->_objectManager->get('\Magento\Catalog\Helper\Category');
        $categories = $_categoryHelper->getStoreCategories(true, false, true);

        return $categories;
    }

    /**
     * @return mixed
     */
    public function getDefaultCustoms()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $customs = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Customs');
        return $customs->getCollection($storeId, 'id_shop');
    }

    /**
     * @return mixed
     */
    public function getCampaigns()
    {
        return $this->getClient()->getCampaigns(['sort' => ['name' => 'asc']]);
    }

    public function getAutomationByParam($param)
    {
        $value = $this->getRequest()->getParam($param);
        $automation = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Automation');
        return $automation->load($value, $param)->getData();
    }

    /**
     * @return array
     */
    public function getAutoresponders()
    {
        $params = ['query' => ['triggerType' => 'onday', 'status' => 'active']];
        $result = $this->getClient()->getAutoresponders($params);
        $autoresponders = [];

        if (!empty($result)) {
            foreach ($result as $autoresponder) {
                if (isset($autoresponder->triggerSettings->selectedCampaigns[0])) {
                    $autoresponders[$autoresponder->triggerSettings->selectedCampaigns[0]][$autoresponder->triggerSettings->dayOfCycle] = [
                        'name' => $autoresponder->name,
                        'subject' => $autoresponder->subject,
                        'dayOfCycle' => $autoresponder->triggerSettings->dayOfCycle
                    ];
                }
            }
        }

        return $autoresponders;
    }

    /**
     * @return array
     */
    public function getAutorespondersForFrontend()
    {
        $autoresponders = $this->getAutoresponders();

        if (empty($autoresponders)) {
            return [];
        }

        $result = [];

        foreach ($autoresponders as $id => $elements) {
            $array = [];
            foreach ($elements as $element) {
                $array[] = $element;
            }

            $result[$id] = $array;
        }
        return $result;
    }
}
