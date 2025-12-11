<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Exception;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\ProductMetadataInterface;

class PlatformVersionProvider
{
    /** @var ModuleListInterface */
    private $moduleList;
    /** @var ProductMetadataInterface */
    private $productMetadata;

    public function __construct(
        ModuleListInterface $moduleList,
        ProductMetadataInterface $productMetadata,
    ) {
        $this->moduleList = $moduleList;
        $this->productMetadata = $productMetadata;
    }

    public function getMagentoVersion(): string
    {
        try {
            return (string)$this->productMetadata->getVersion();
        } catch (Exception $ex) {
            return '';
        }
    }

    public function getPhpVersion(): string
    {
        return PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
    }

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
