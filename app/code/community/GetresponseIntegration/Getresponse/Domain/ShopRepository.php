<?php
use GetresponseIntegration_Getresponse_Domain_Shop as Shop;
use GetresponseIntegration_Getresponse_Domain_ShopFactory as ShopFactory;

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
        \Mage::getConfig()->cleanCache();
    }

    public function create(Shop $shop)
    {
        \Mage::getConfig()->saveConfig($this->configPath, json_encode($shop->toArray()), 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    public function update(Shop $shop)
    {
        $shopDb = json_decode(\Mage::getStoreConfig($this->configPath), true);
        $shopUpdated = json_encode(array_replace($shopDb, $shop->toArray()));
        \Mage::getConfig()->saveConfig($this->configPath, $shopUpdated, 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    public function getShop()
    {
        return ShopFactory::createFromArray(json_decode(\Mage::getStoreConfig($this->configPath), true));
    }
}