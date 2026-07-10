<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Setup\Patch\Data;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Cache\Type\Config as FrameworkCacheType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\PageCache\Model\Cache\Type as PageCacheType;
use Magento\Store\Model\Store;

class RemoveUnusedConfigData implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var WriterInterface */
    private $configWriter;

    /** @var Manager */
    private $cacheManager;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface $configWriter
     * @param Manager $cacheManager
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $configWriter,
        Manager $cacheManager
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Remove unused integration config values.
     */
    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

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

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    /**
     * Return patch dependencies.
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Return patch aliases.
     */
    public function getAliases(): array
    {
        return [];
    }
}
