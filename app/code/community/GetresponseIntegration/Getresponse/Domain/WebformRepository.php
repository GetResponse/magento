<?php

use GetresponseIntegration_Getresponse_Domain_Webform as Webform;
use GetresponseIntegration_Getresponse_Domain_WebformFactory as WebformFactory;

/**
 * Class GetresponseIntegration_Getresponse_Domain_WebformRepository
 */
class GetresponseIntegration_Getresponse_Domain_WebformRepository
{
    /** @var string */
    private $configPath = 'getresponse/webform';

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
     * @param GetresponseIntegration_Getresponse_Domain_Webform $webform
     */
    public function create(Webform $webform)
    {
        \Mage::getConfig()->saveConfig($this->configPath, json_encode($webform->toArray()), 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    /**
     * @param GetresponseIntegration_Getresponse_Domain_Webform $webform
     */
    public function update(Webform $webform)
    {
        $webformDb = json_decode(\Mage::getStoreConfig($this->configPath), true);

        foreach ($webform->toArray() as $name => $setting) {
            if (!empty($setting)) {
                $webformDb[$name] = $setting;
            }
        }
        \Mage::getConfig()->saveConfig($this->configPath, json_encode($webformDb), 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    /**
     * @return GetresponseIntegration_Getresponse_Domain_Webform
     */
    public function getWebform()
    {
        return WebformFactory::createFromArray(json_decode(\Mage::getStoreConfig($this->configPath), true));
    }
}
