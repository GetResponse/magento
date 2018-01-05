<?php
use GetresponseIntegration_Getresponse_Domain_CustomFieldsCollection as CustomFieldsCollection;

class GetresponseIntegration_Getresponse_Domain_CustomFieldsCollectionRepository
{
    private $configPath = 'getresponse/customFields';
    private $shopId;

    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    public function delete()
    {
        \Mage::getConfig()->deleteConfig($this->configPath, 'default', $this->shopId);
    }

    public function create(CustomFieldsCollection $customFieldsCollection)
    {
        \Mage::getConfig()->saveConfig($this->configPath, json_encode($customFieldsCollection->toArray()), 'default', $this->shopId);
    }

    public function getCollection()
    {
        return json_decode(\Mage::getStoreConfig($this->configPath), true);
    }
}