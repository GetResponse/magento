<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;

class GeneralPresenter
{
    private $pluginVersion;
    private $pluginMode;
    private $scope;

    public function __construct(
        string $pluginVersion,
        PluginMode $pluginMode,
        Scope $scope
    ) {
        $this->pluginVersion = $pluginVersion;
        $this->pluginMode = $pluginMode;
        $this->scope = $scope;
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

    /**
     * @return int
     */
    public function getScope(): int
    {
         return (int) $this->scope->getScopeId();
    }
}
