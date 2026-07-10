<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Exception;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;

class PlatformVersionProvider
{
    /** @var ModuleListInterface */
    private $moduleList;
    /** @var ProductMetadataInterface */
    private $productMetadata;

    /**
     * @param ModuleListInterface $moduleList
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ModuleListInterface $moduleList,
        ProductMetadataInterface $productMetadata
    ) {
        $this->moduleList = $moduleList;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Get magento version.
     */
    public function getMagentoVersion(): string
    {
        try {
            return (string)$this->productMetadata->getVersion();
        } catch (Exception $ex) {
            return '';
        }
    }

    /**
     * Get php version.
     */
    public function getPhpVersion(): string
    {
        return PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
    }

    /**
     * Get plugin version.
     */
    public function getPluginVersion(): string
    {
        try {
            $moduleInfo = $this->moduleList->getOne('GetResponse_GetResponseIntegration');
            return (string)($moduleInfo['setup_version'] ?? '');
        } catch (Exception $ex) {
            return '';
        }
    }
}
