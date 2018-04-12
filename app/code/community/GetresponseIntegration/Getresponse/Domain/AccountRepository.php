<?php
use GetresponseIntegration_Getresponse_Domain_Account as Account;
use GetresponseIntegration_Getresponse_Domain_AccountFactory as AccountFactory;

/**
 * Class GetresponseIntegration_Getresponse_Domain_AccountRepository
 */
class GetresponseIntegration_Getresponse_Domain_AccountRepository
{
    private $configPath = 'getresponse/account';
    private $shopId;

    /**
     * GetresponseIntegration_Getresponse_Domain_AccountRepository constructor.
     * @param $shopId - shopId
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
     * @param GetresponseIntegration_Getresponse_Domain_Account $account
     */
    public function create(Account $account)
    {
        \Mage::getConfig()->saveConfig($this->configPath, json_encode($account->toArray()), 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    /**
     * @param GetresponseIntegration_Getresponse_Domain_Account $account
     */
    public function update(Account $account)
    {
        $accountDb = json_decode(\Mage::getStoreConfig($this->configPath), true);
        $accountUpdated = json_encode(array_replace($accountDb, $account->toArray()));
        \Mage::getConfig()->saveConfig($this->configPath, $accountUpdated, 'default', $this->shopId);
        \Mage::getConfig()->cleanCache();
    }

    /**
     * @return GetresponseIntegration_Getresponse_Domain_Account
     */
    public function getAccount()
    {
        $account = AccountFactory::createFromArray(json_decode(\Mage::getStoreConfig($this->configPath), true));
        return $account;
    }
}
