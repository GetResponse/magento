<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\FacebookAdsPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookBusinessExtension;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\Magento\WebForm;

class SectionsListPresenter
{
    private $facebookPixel;
    private $facebookAdsPixel;
    private $facebookBusinessExtension;
    private $webForm;
    private $webEventTracking;
    private $liveSynchronization;

    public function __construct(
        FacebookPixel $facebookPixel,
        FacebookAdsPixel $facebookAdsPixel,
        FacebookBusinessExtension $facebookBusinessExtension,
        WebForm $webForm,
        WebEventTracking $webEventTracking,
        LiveSynchronization $liveSynchronization
    ) {
        $this->facebookPixel = $facebookPixel;
        $this->facebookAdsPixel = $facebookAdsPixel;
        $this->facebookBusinessExtension = $facebookBusinessExtension;
        $this->webForm = $webForm;
        $this->webEventTracking = $webEventTracking;
        $this->liveSynchronization = $liveSynchronization;
    }


    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\SnippetPresenter
     */
    public function getFacebookPixel(): SnippetPresenter
    {
        return new SnippetPresenter($this->facebookPixel);
    }

    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\SnippetPresenter
     */
    public function getFacebookAdsPixel(): SnippetPresenter
    {
        return new SnippetPresenter($this->facebookAdsPixel);
    }

    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\SnippetPresenter
     */
    public function getFacebookBusinessExtension(): SnippetPresenter
    {
        return new SnippetPresenter($this->facebookBusinessExtension);
    }

    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\WebFormPresenter
     */
    public function getWebforms(): WebFormPresenter
    {
        return new WebFormPresenter($this->webForm);
    }

    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\SnippetPresenter
     */
    public function getWebEventTracking(): SnippetPresenter
    {
        return new SnippetPresenter($this->webEventTracking);
    }

    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\LiveSynchronizationPresenter
     */
    public function getLiveSynchronization(): LiveSynchronizationPresenter
    {
        return new LiveSynchronizationPresenter($this->liveSynchronization);
    }
}
