<?php
use GetresponseIntegration_Getresponse_Domain_AutomationRulesCollection as AutomationRulesCollection;

/**
 * Class GetresponseIntegration_Getresponse_Domain_AutomationRulesCollectionRepository
 */
class GetresponseIntegration_Getresponse_Domain_AutomationRulesCollectionRepository
{
    /** @var string */
    private $configPath = 'getresponse/automationRules';

    /** @var string */
    private $shopId;

    /**
     * @param string $shopId
     */
    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    public function delete()
    {
        \Mage::getConfig()->deleteConfig($this->configPath, 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    /**
     * @param GetresponseIntegration_Getresponse_Domain_AutomationRulesCollection $automationRuleCollection
     */
    public function create(AutomationRulesCollection $automationRuleCollection)
    {
        \Mage::getConfig()->saveConfig($this->configPath, json_encode($automationRuleCollection->toArray()), 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    /**
     * @return array
     */
    public function getCollection()
    {
        $data = json_decode(\Mage::getStoreConfig($this->configPath), true);
        return is_array($data) ? $data : array();
    }
}