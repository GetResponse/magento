<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

class General
{
    /** @var string */
    private $pluginVersion;
    /** @var string */
    private $magentoVersion;
    /** @var string */
    private $phpVersion;

    /**
     * @param string $pluginVersion
     * @param string $magentoVersion
     * @param string $phpVersion
     */
    public function __construct(
        string $pluginVersion,
        string $magentoVersion,
        string $phpVersion
    ) {
        $this->pluginVersion = $pluginVersion;
        $this->magentoVersion = $magentoVersion;
        $this->phpVersion = $phpVersion;
    }

    /**
     * Get plugin version.
     *
     * @return string
     */
    public function getPluginVersion(): string
    {
        return $this->pluginVersion;
    }

    /**
     * Get mode.
     *
     * @return string
     */
    public function getMode(): string
    {
        return 'new';
    }

    /**
     * Get php version.
     *
     * @return string
     */
    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }

    /**
     * Get magento version.
     *
     * @return string
     */
    public function getMagentoVersion(): string
    {
        return $this->magentoVersion;
    }
}
