<?php
namespace GetResponse\GetResponseIntegration\Setup;

use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettings;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Account;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Rule;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\WebformSettings;
use Magento\Framework\App\Cache\Manager;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;

/**
 * Class UpgradeData
 * @package GetResponse\GetResponseIntegration\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /** @var WriterInterface */
    private $configWriter;

    /** @var Manager */
    private $cacheManager;

    /**
     * @param WriterInterface $configWriter
     * @param Manager $cacheManager
     */
    public function __construct(
        WriterInterface $configWriter,
        Manager $cacheManager
    ) {
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '20.1.1', '<=')) {

            $this->ver2011updateConnectionSettings($setup);
            $this->ver2011updateRegistrationSettings($setup);
            $this->ver2011migrateAccountSettings($setup);
            $this->ver2011migrateRulesSettings($setup);
            $this->ver2011migrateCustomFieldsSettings($setup);
            $this->ver2011migrateWebformSettings($setup);
            $this->ver2011migrateWebEventTrackingSettings($setup);

            $this->ver2011removeUnusedTables($setup);

            $this->cacheManager->clean(['config']);
        }

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function ver2011updateConnectionSettings(ModuleDataSetupInterface $setup)
    {
        $sql = "SELECT api_key, api_url, api_domain FROM " . $setup->getTable('getresponse_settings');
        $data = $setup->getConnection()->fetchAll($sql);

        if (0 === count($data)) {
            return;
        }

        foreach ($data as $row) {
            $payload = [
                'apiKey' => $row['api_key'],
                'url' => $row['api_url'],
                'domain' => $row['api_domain']
            ];

            $settings = ConnectionSettingsFactory::createFromArray($payload);

            $this->configWriter->save(
                Config::CONFIG_DATA_CONNECTION_SETTINGS,
                json_encode($settings->toArray()),
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            );
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function ver2011migrateAccountSettings(ModuleDataSetupInterface $setup)
    {
        $sql = "SELECT * FROM " . $setup->getTable('getresponse_account');
        $data = $setup->getConnection()->fetchAll($sql);

        if (0 === count($data)) {
            return;
        }

        foreach ($data as $row) {

            $account = new Account(
                $row['account_id'],
                $row['first_name'],
                $row['last_name'],
                $row['email'],
                $row['company_name'],
                $row['phone'],
                $row['state'],
                $row['city'],
                $row['street'],
                $row['zip_code'],
                $row['country_code']
            );

            $this->configWriter->save(
                Config::CONFIG_DATA_ACCOUNT,
                json_encode($account->toArray()),
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            );
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function ver2011migrateRulesSettings(ModuleDataSetupInterface $setup)
    {
        $sql = "SELECT * FROM " . $setup->getTable('getresponse_automation');
        $data = $setup->getConnection()->fetchAll($sql);

        if (0 === count($data)) {
            return;
        }

        $rules = [];
        foreach ($data as $row) {

            $rule = new Rule(
                uniqid(),
                $row['category_id'],
                $row['action'],
                $row['campaign_id'],
                $row['cycle_day']
            );

            $rules[] = $rule->asArray();
        }

        $this->configWriter->save(
            Config::CONFIG_DATA_RULES,
            json_encode($rules),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function ver2011migrateCustomFieldsSettings(ModuleDataSetupInterface $setup)
    {
        $sql = "SELECT * FROM " . $setup->getTable('getresponse_customs');
        $data = $setup->getConnection()->fetchAll($sql);

        if (0 === count($data)) {
            return;
        }

        $customFields = [];
        foreach ($data as $row) {

            $customFields[] = (new CustomField(
                $row['id'],
                $row['custom_field'],
                $row['custom_value'],
                $row['custom_name'],
                $row['default'],
                $row['active_custom']
            ))->toArray();
        }

        $this->configWriter->save(
            Config::CONFIG_DATA_REGISTRATION_CUSTOMS,
            json_encode($customFields),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function ver2011migrateWebformSettings(ModuleDataSetupInterface $setup)
    {
        $sql = "SELECT * FROM " . $setup->getTable('getresponse_webform');
        $data = $setup->getConnection()->fetchAll($sql);

        if (0 === count($data)) {
            return;
        }

        foreach ($data as $row) {

            $webform = new WebformSettings(
                $row['active_subscription'],
                $row['url'],
                $row['webform_id'],
                $row['sidebar']
            );

            $this->configWriter->save(
                Config::CONFIG_DATA_WEBFORMS,
                json_encode($webform->toArray()),
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            );
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function ver2011updateRegistrationSettings(ModuleDataSetupInterface $setup)
    {
        $sql = "SELECT * FROM " . $setup->getTable('getresponse_settings');
        $data = $setup->getConnection()->fetchAll($sql);

        if (0 === count($data)) {
            return;
        }

        foreach ($data as $row) {

            $registrationSettings = new RegistrationSettings(
                $row['active_subscription'],
                $row['update'],
                $row['campaign_id'],
                $row['cycle_day']
            );

            $this->configWriter->save(
                Config::CONFIG_DATA_REGISTRATION_SETTINGS,
                json_encode($registrationSettings->toArray()),
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            );
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function ver2011migrateWebEventTrackingSettings(ModuleDataSetupInterface $setup)
    {
        $sql = "SELECT * FROM " . $setup->getTable('getresponse_settings');
        $data = $setup->getConnection()->fetchAll($sql);

        if (0 === count($data)) {
            return;
        }

        foreach ($data as $row) {

            $webEventTracking = new WebEventTrackingSettings(
                $row['web_traffic'],
                $row['feature_tracking'],
                $row['tracking_code_snippet']
            );

            $this->configWriter->save(
                Config::CONFIG_DATA_WEB_EVENT_TRACKING,
                json_encode($webEventTracking->toArray()),
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            );
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function ver2011removeUnusedTables(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_account'));
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_automation'));
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_customs'));
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_settings'));
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_webform'));
    }
}
