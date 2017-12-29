<?php
use GetresponseIntegration_Getresponse_Domain_Shop as Shop;

class GetresponseIntegration_Getresponse_Domain_ShopRepository
{
    private $configPath = 'getresponse/shop';
    private $shopId;

    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    public function delete()
    {
        \Mage::getConfig()->deleteConfig($this->configPath, 'default', $this->shopId);
    }

    public function create(Shop $shop)
    {
        \Mage::getConfig()->saveConfig($this->configPath, json_encode($shop->toArray()), 'default', $this->shopId);
    }

    public function update(Shop $shop)
    {
        $shopDb = json_decode(\Mage::getStoreConfig($this->configPath), true);
        $shopUpdated = json_encode(array_replace($shopDb, $shop->toArray()));
        \Mage::getConfig()->saveConfig($this->configPath, $shopUpdated, 'default', $this->shopId);
    }

    public function getShop()
    {
        return json_decode(\Mage::getStoreConfig($this->configPath), true);
    }
}