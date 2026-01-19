<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Setup;

use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Cache\Type\Config as FrameworkCacheType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\PageCache\Model\Cache\Type as PageCacheType;
use Magento\Store\Model\Store;

class Uninstall implements UninstallInterface
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

    /**
     * Invoked when remove-data flag is set during module uninstall.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $connection = $setup->getConnection();

        $tablesToRemove = [
            'getresponse_account',
            'getresponse_automation',
            'getresponse_customs',
            'getresponse_settings',
            'getresponse_webform',
            'getresponse_cart_map',
            'getresponse_order_map',
            'getresponse_product_map'
        ];

        foreach ($tablesToRemove as $tableToRemove) {
            $tableName = $setup->getTable($tableToRemove);

            if ($connection->isTableExists($tableName)) {
                $connection->dropTable($tableName);
            }
        }

        $coreConfigDataToRemove = [
            'getresponse/shop/status',
            'getresponse/shop/id',
            'getresponse/ecommerce/list/id',
            'getresponse/account',
            'getresponse/connection-settings',
            'getresponse/registration/settings',
            'getresponse/registration/customs',
            'getresponse/invalid_request_date_time',
            Config::CONFIG_DATA_WEB_EVENT_TRACKING,
            Config::CONFIG_DATA_WEBFORMS_SETTINGS,
            Config::CONFIG_LIVE_SYNCHRONIZATION,
            Config::CONFIG_DATA_FACEBOOK_PIXEL_SNIPPET,
            Config::CONFIG_DATA_FACEBOOK_ADS_PIXEL_SNIPPET,
            Config::CONFIG_DATA_FACEBOOK_BUSINESS_EXTENSION_SNIPPET
        ];

        foreach ($coreConfigDataToRemove as $configDataToRemove) {

            $this->configWriter->delete(
                $configDataToRemove,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            );
        }

        $this->cacheManager->clean([
            FrameworkCacheType::TYPE_IDENTIFIER,
            PageCacheType::TYPE_IDENTIFIER
        ]);
    }
}
