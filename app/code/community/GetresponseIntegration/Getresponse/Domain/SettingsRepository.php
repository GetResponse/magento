<?php
use GetresponseIntegration_Getresponse_Domain_Settings as Settings;

class GetresponseIntegration_Getresponse_Domain_SettingsRepository
{
    private $configPath = 'getresponse/settings';
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

    public function create(Settings $settings)
    {
        \Mage::getConfig()->saveConfig($this->configPath, json_encode($settings->toArray()), 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    public function update(Settings $settings)
    {
        $settingsDb = json_decode(\Mage::getStoreConfig($this->configPath), true);

        foreach ($settings->toArray() as $name => $setting) {
            if ($setting !== '') {
                $settingsDb[$name] = $setting;
            }
        }

        \Mage::getConfig()->saveConfig($this->configPath, json_encode($settingsDb), 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    public function getAccount()
    {
        return json_decode(\Mage::getStoreConfig($this->configPath), true);
    }
}