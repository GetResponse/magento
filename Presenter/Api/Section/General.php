<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;

class General
{
    private $pluginVersion;
    private $pluginMode;

    public function __construct(string $pluginVersion, PluginMode $pluginMode)
    {
        $this->pluginVersion = $pluginVersion;
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
}
