<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\FacebookAdsPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookBusinessExtension;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization as LiveSynchronizationDTO;
use GetResponse\GetResponseIntegration\Domain\Magento\Recommendation;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\Magento\WebForm as WebFormDTO;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;

class Store
{
    private $scope;
    private $facebookPixel;
    private $facebookAdsPixel;
    private $facebookBusinessExtension;
    private $webForm;
    private $webEventTracking;
    private $liveSynchronization;
    private $recommendation;

    public function __construct(
        Scope $scope,
        FacebookPixel $facebookPixel,
        FacebookAdsPixel $facebookAdsPixel,
        FacebookBusinessExtension $facebookBusinessExtension,
        WebFormDTO $webForm,
        WebEventTracking $webEventTracking,
        LiveSynchronizationDTO $liveSynchronization,
        Recommendation $recommendation
    ) {
        $this->scope = $scope;
        $this->facebookPixel = $facebookPixel;
        $this->facebookAdsPixel = $facebookAdsPixel;
        $this->facebookBusinessExtension = $facebookBusinessExtension;
        $this->webForm = $webForm;
        $this->webEventTracking = $webEventTracking;
        $this->liveSynchronization = $liveSynchronization;
        $this->recommendation = $recommendation;
    }

    /**
     * @return int
     */
    public function getStoreId(): int
    {
        return (int) $this->scope->getScopeId();
    }

    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\Snippet
     */
    public function getFacebookPixel(): Snippet
    {
        return new Snippet($this->facebookPixel);
    }

    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\Snippet
     */
    public function getFacebookAdsPixel(): Snippet
    {
        return new Snippet($this->facebookAdsPixel);
    }

    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\Snippet
     */
    public function getFacebookBusinessExtension(): Snippet
    {
        return new Snippet($this->facebookBusinessExtension);
    }

    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\WebForm
     */
    public function getWebForm(): WebForm
    {
        return new WebForm($this->webForm);
    }

    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\Snippet
     */
    public function getWebEventTracking(): Snippet
    {
        return new Snippet($this->webEventTracking);
    }

    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\LiveSynchronization
     */
    public function getLiveSynchronization(): LiveSynchronization
    {
        return new LiveSynchronization($this->liveSynchronization);
    }

    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\Snippet
     */
    public function getRecommendation(): Snippet
    {
        return new Snippet($this->recommendation);
    }
}
