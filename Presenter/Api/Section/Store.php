<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\FacebookAdsPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookBusinessExtension;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization as LiveSynchronizationDTO;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\Magento\WebForm as WebFormDTO;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;

class Store
{
    /** @var Scope */
    private $scope;
    /** @var FacebookPixel */
    private $facebookPixel;
    /** @var FacebookAdsPixel */
    private $facebookAdsPixel;
    /** @var FacebookBusinessExtension */
    private $facebookBusinessExtension;
    /** @var WebFormDTO */
    private $webForm;
    /** @var WebEventTracking */
    private $webEventTracking;
    /** @var LiveSynchronizationDTO */
    private $liveSynchronization;

    /**
     * @param Scope $scope
     * @param FacebookPixel $facebookPixel
     * @param FacebookAdsPixel $facebookAdsPixel
     * @param FacebookBusinessExtension $facebookBusinessExtension
     * @param WebFormDTO $webForm
     * @param WebEventTracking $webEventTracking
     * @param LiveSynchronizationDTO $liveSynchronization
     */
    public function __construct(
        Scope $scope,
        FacebookPixel $facebookPixel,
        FacebookAdsPixel $facebookAdsPixel,
        FacebookBusinessExtension $facebookBusinessExtension,
        WebFormDTO $webForm,
        WebEventTracking $webEventTracking,
        LiveSynchronizationDTO $liveSynchronization
    ) {
        $this->scope = $scope;
        $this->facebookPixel = $facebookPixel;
        $this->facebookAdsPixel = $facebookAdsPixel;
        $this->facebookBusinessExtension = $facebookBusinessExtension;
        $this->webForm = $webForm;
        $this->webEventTracking = $webEventTracking;
        $this->liveSynchronization = $liveSynchronization;
    }

    /**
     * Get store id.
     *
     * @return int
     */
    public function getStoreId(): int
    {
        return (int) $this->scope->getScopeId();
    }

    /**
     * Get facebook pixel.
     *
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\Snippet
     */
    public function getFacebookPixel(): Snippet
    {
        return new Snippet($this->facebookPixel);
    }

    /**
     * Get facebook ads pixel.
     *
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\Snippet
     */
    public function getFacebookAdsPixel(): Snippet
    {
        return new Snippet($this->facebookAdsPixel);
    }

    /**
     * Get facebook business extension.
     *
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\Snippet
     */
    public function getFacebookBusinessExtension(): Snippet
    {
        return new Snippet($this->facebookBusinessExtension);
    }

    /**
     * Get web form.
     *
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\WebForm
     */
    public function getWebForm(): WebForm
    {
        return new WebForm($this->webForm);
    }

    /**
     * Get web event tracking.
     *
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\Snippet
     */
    public function getWebEventTracking(): Snippet
    {
        return new Snippet($this->webEventTracking);
    }

    /**
     * Get live synchronization.
     *
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\LiveSynchronization
     */
    public function getLiveSynchronization(): LiveSynchronization
    {
        return new LiveSynchronization($this->liveSynchronization);
    }
}
