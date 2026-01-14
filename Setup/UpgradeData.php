<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Setup;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Store\Model\StoreManagerInterface;

class UpgradeData implements UpgradeDataInterface
{
    private $configWriter;
    private $cacheManager;
    private $storeManager;

    public function __construct(
        WriterInterface $configWriter,
        Manager $cacheManager,
        StoreManagerInterface $storeManager
    ) {
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
        $this->storeManager = $storeManager;
    }

    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ): void {
        $setup->startSetup();

        $this->cleanupDatabase($setup);

        $setup->endSetup();
    }

    private function cleanupDatabase(ModuleDataSetupInterface $setup): void
    {
    }
}
