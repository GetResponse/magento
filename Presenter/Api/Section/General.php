<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;

class General
{
    private $pluginVersion;

    public function __construct(string $pluginVersion)
    {
        $this->pluginVersion = $pluginVersion;
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
}
