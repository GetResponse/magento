<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;

class General
{
    private $pluginVersion;
    private $magentoVersion;
    private $phpVersion;

    public function __construct(
        string $pluginVersion,
        string $magentoVersion,
        string $phpVersion,
    ) {
        $this->pluginVersion = $pluginVersion;
        $this->magentoVersion = $magentoVersion;
        $this->phpVersion = $phpVersion;
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
        return 'new';
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
