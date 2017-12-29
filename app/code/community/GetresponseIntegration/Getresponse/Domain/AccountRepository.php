<?php
use GetresponseIntegration_Getresponse_Domain_Account as Account;

class GetresponseIntegration_Getresponse_Domain_AccountRepository
{
    private $configPath = 'getresponse/account';
    private $shopId;

    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    public function delete()
    {
        \Mage::getConfig()->deleteConfig($this->configPath, 'default', $this->shopId);
    }

    public function create(Account $account)
    {
        \Mage::getConfig()->saveConfig($this->configPath, json_encode($account->toArray()), 'default', $this->shopId);
    }

    public function update(Account $account)
    {
        $accountDb = json_decode(\Mage::getStoreConfig($this->configPath), true);
        $accountUpdated = json_encode(array_replace($accountDb, $account->toArray()));
        \Mage::getConfig()->saveConfig($this->configPath, $accountUpdated, 'default', $this->shopId);
    }

    public function getAccount()
    {
        return json_decode(\Mage::getStoreConfig($this->configPath), true);
    }
}