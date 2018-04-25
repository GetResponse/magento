<?php
use GetresponseIntegration_Getresponse_Domain_Shop as Shop;
use GetresponseIntegration_Getresponse_Domain_ShopFactory as ShopFactory;

/**
 * Class GetresponseIntegration_Getresponse_Domain_ShopRepository
 */
class GetresponseIntegration_Getresponse_Domain_ShopRepository
{
    /** @var string */
    private $configPath = 'getresponse/shop';

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
     * @param GetresponseIntegration_Getresponse_Domain_Shop $shop
     */
    public function create(Shop $shop)
    {
        \Mage::getConfig()->saveConfig($this->configPath, json_encode($shop->toArray()), 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    /**
     * @param GetresponseIntegration_Getresponse_Domain_Shop $shop
     */
    public function update(Shop $shop)
    {
        $shopDb = json_decode(\Mage::getStoreConfig($this->configPath), true);
        $shopUpdated = json_encode(array_replace($shopDb, $shop->toArray()));
        \Mage::getConfig()->saveConfig($this->configPath, $shopUpdated, 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    /**
     * @return GetresponseIntegration_Getresponse_Domain_Shop
     */
    public function getShop()
    {
        return ShopFactory::createFromArray(json_decode(\Mage::getStoreConfig($this->configPath), true));
    }
}
