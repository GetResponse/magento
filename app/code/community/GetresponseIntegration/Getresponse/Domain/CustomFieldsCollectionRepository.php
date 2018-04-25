<?php
use GetresponseIntegration_Getresponse_Domain_CustomFieldsCollection as CustomFieldsCollection;

/**
 * Class GetresponseIntegration_Getresponse_Domain_CustomFieldsCollectionRepository
 */
class GetresponseIntegration_Getresponse_Domain_CustomFieldsCollectionRepository
{
    /** @var string */
    private $configPath = 'getresponse/customFields';

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
     * @param GetresponseIntegration_Getresponse_Domain_CustomFieldsCollection $customFieldsCollection
     */
    public function create(CustomFieldsCollection $customFieldsCollection)
    {
        \Mage::getConfig()->saveConfig($this->configPath, json_encode($customFieldsCollection->toArray()), 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    /**
     * @return array
     */
    public function getCollection()
    {
        return json_decode(\Mage::getStoreConfig($this->configPath), true);
    }
}