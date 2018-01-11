<?php
use GetresponseIntegration_Getresponse_Domain_AutomationRulesCollection as AutomationRulesCollection;

class GetresponseIntegration_Getresponse_Domain_AutomationRulesCollectionRepository
{
    private $configPath = 'getresponse/automationRules';
    private $shopId;

    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    public function delete()
    {
        \Mage::getConfig()->deleteConfig($this->configPath, 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    public function create(AutomationRulesCollection $automationRuleCollection)
    {
        \Mage::getConfig()->saveConfig($this->configPath, json_encode($automationRuleCollection->toArray()), 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    public function getCollection()
    {
        return json_decode(\Mage::getStoreConfig($this->configPath), true);
    }
}