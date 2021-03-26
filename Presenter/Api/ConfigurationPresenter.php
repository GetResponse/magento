<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\FacebookAdsPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookBusinessExtension;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\Magento\WebForm;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Presenter\Api\Section\GeneralPresenter;
use GetResponse\GetResponseIntegration\Presenter\Api\Section\SectionsListPresenter;

class ConfigurationPresenter
{
    private $pluginVersion;
    private $pluginMode;
    private $scope;
    private $facebookPixel;
    private $facebookAdsPixel;
    private $facebookBusinessExtension;
    private $webForm;
    private $webEventTracking;
    private $liveSynchronization;

    public function __construct(
        string $pluginVersion,
        PluginMode $pluginMode,
        Scope $scope,
        FacebookPixel $facebookPixel,
        FacebookAdsPixel $facebookAdsPixel,
        FacebookBusinessExtension $facebookBusinessExtension,
        WebForm $webForm,
        WebEventTracking $webEventTracking,
        LiveSynchronization $liveSynchronization
    ) {
        $this->pluginVersion = $pluginVersion;
        $this->pluginMode = $pluginMode;
        $this->scope = $scope;
        $this->facebookPixel = $facebookPixel;
        $this->facebookAdsPixel = $facebookAdsPixel;
        $this->facebookBusinessExtension = $facebookBusinessExtension;
        $this->webForm = $webForm;
        $this->webEventTracking = $webEventTracking;
        $this->liveSynchronization = $liveSynchronization;
    }

    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\GeneralPresenter
     */
    public function getGeneral(): GeneralPresenter
    {
        return new GeneralPresenter(
            $this->pluginVersion,
            $this->pluginMode,
            $this->scope
        );
    }

    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\SectionsListPresenter
     */
    public function getSections(): SectionsListPresenter
    {
        return new SectionsListPresenter(
            $this->facebookPixel,
            $this->facebookAdsPixel,
            $this->facebookBusinessExtension,
            $this->webForm,
            $this->webEventTracking,
            $this->liveSynchronization
        );
    }
}
