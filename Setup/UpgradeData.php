<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Setup;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistration;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\Magento\WebForm;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Store\Model\Store;

class UpgradeData implements UpgradeDataInterface
{
    private $configWriter;
    private $cacheManager;

    public function __construct(
        WriterInterface $configWriter,
        Manager $cacheManager
    ) {
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
    }

    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1', '>')
            && version_compare($context->getVersion(), '20.1.1', '<=')) {

            $this->ver2011updateConnectionSettings($setup);
            $this->ver2011updateRegistrationSettings($setup);
            $this->ver2011migrateAccountSettings($setup);
            $this->ver2011migrateCustomFieldsSettings($setup);
            $this->ver2011migrateWebformSettings($setup);
            $this->ver2011migrateWebEventTrackingSettings($setup);
            $this->cacheManager->clean(['config']);
        }

        if (version_compare($context->getVersion(), '20.1.1', '>=')
            && version_compare($context->getVersion(), '20.3.4', '<=')) {

            $this->ver2034migrateCustomFieldsMapping();
        }

        $setup->endSetup();
    }

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

            try {
                $settings = ConnectionSettingsFactory::createFromArray($payload);
                $this->configWriter->save(
                    Config::CONFIG_DATA_CONNECTION_SETTINGS,
                    json_encode($settings->toArray()),
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    Store::DEFAULT_STORE_ID
                );
            } catch (ConnectionSettingsException $e) {
            }
        }
    }

    private function ver2011migrateAccountSettings(ModuleDataSetupInterface $setup)
    {
        $sql = "SELECT * FROM " . $setup->getTable('getresponse_account');
        $data = $setup->getConnection()->fetchAll($sql);

        if (0 === count($data)) {
            return;
        }

        foreach ($data as $row) {


            $data = [
                'firstName' => $row['first_name'],
                'lastName' => $row['first_name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'companyName' => $row['company_name'],
                'city' => $row['city'],
                'street' => $row['street']
            ];

            $this->configWriter->save(
                Config::CONFIG_DATA_ACCOUNT,
                json_encode($data),
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            );
        }
    }

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
            $webform = new WebForm(
                $row['active_subscription'],
                $row['url'],
                $row['webform_id'],
                $row['sidebar']
            );

            $this->configWriter->save(
                Config::CONFIG_DATA_WEBFORMS_SETTINGS,
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
            $registrationSettings = new SubscribeViaRegistration(
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
            $webEventTracking = new WebEventTracking($row['web_traffic'], $row['tracking_code_snippet']);

            $this->configWriter->save(
                Config::CONFIG_DATA_WEB_EVENT_TRACKING,
                json_encode($webEventTracking->toArray()),
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            );
        }
    }

    private function ver2034migrateCustomFieldsMapping()
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_REGISTRATION_CUSTOMS,
            json_encode(CustomFieldsMappingCollection::createDefaults()->toArray()),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }
}
