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
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_account'));
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_automation'));
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_customs'));
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_settings'));
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_webform'));
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_product_map'));

        $this->configWriter->delete(
            Config::CONFIG_DATA_SHOP_STATUS,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->configWriter->delete(
            Config::CONFIG_DATA_SHOP_ID,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->configWriter->delete(
            Config::CONFIG_DATA_ECOMMERCE_LIST_ID,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->configWriter->delete(
            Config::CONFIG_DATA_ACCOUNT,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->configWriter->delete(
            Config::CONFIG_DATA_CONNECTION_SETTINGS,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->configWriter->delete(
            Config::CONFIG_DATA_WEB_EVENT_TRACKING,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->configWriter->delete(
            Config::CONFIG_DATA_REGISTRATION_SETTINGS,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->configWriter->delete(
            Config::CONFIG_DATA_REGISTRATION_CUSTOMS,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->configWriter->delete(
            Config::CONFIG_DATA_WEBFORMS_SETTINGS,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->configWriter->delete(
            Config::INVALID_REQUEST_DATE_TIME,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean([
            FrameworkCacheType::TYPE_IDENTIFIER,
            PageCacheType::TYPE_IDENTIFIER
        ]);
    }
}
