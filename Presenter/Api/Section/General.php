<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;

class General
{
    private $pluginVersion;
    private $magentoVersion;
    private $phpVersion;
    private $pluginMode;

    public function __construct(
        string $pluginVersion,
        string $magentoVersion,
        string $phpVersion,
        PluginMode $pluginMode
    ) {
        $this->pluginVersion = $pluginVersion;
        $this->magentoVersion = $magentoVersion;
        $this->phpVersion = $phpVersion;
        $this->pluginMode = $pluginMode;
    }

    /**
     * @return string
     */
    public function getPluginVersion(): string
    {
        return $this->pluginVersion;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->pluginMode->getMode();
    }

    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }

    public function getMagentoVersion(): string
    {
        return $this->magentoVersion;
    }

}
