<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Setup;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Cache\Type\Config as FrameworkCacheType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\PageCache\Model\Cache\Type as PageCacheType;
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
    ): void {
        $this->cleanupDatabase();
    }

    // phpcs:ignore
    private function cleanupDatabase(): void
    {
        $coreConfigDataToRemove = [
            'getresponse/shop/status',
            'getresponse/shop/id',
            'getresponse/ecommerce/list/id',
            'getresponse/account',
            'getresponse/connection-settings',
            'getresponse/registration/settings',
            'getresponse/registration/customs',
            'getresponse/invalid_request_date_time'
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
