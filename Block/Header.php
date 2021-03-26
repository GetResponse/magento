<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\Magento\FacebookAdsPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookBusinessExtension;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Header extends Template
{
    private $repository;
    private $magentoStore;

    public function __construct(
        Context $context,
        Repository $repository,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->magentoStore = $magentoStore;
    }

    public function getTrackingData(): array
    {
        return [
            'trackingCodeSnippet' => $this->findTrackingCodeSnippet(),
            'facebookPixelCodeSnippet' => $this->findFacebookPixelSnippet(),
            'facebookAdsPixelCodeSnippet' => $this->findFacebookAdsPixelSnippet(),
            'facebookBusinessExtensionCodeSnippet' => $this->findFacebookBusinessExtensionSnippet(),
        ];
    }

    private function findTrackingCodeSnippet(): ?string
    {
        $webEventTracking = WebEventTracking::createFromRepository(
            $this->repository->getWebEventTracking(
                $this->magentoStore->getCurrentScope()->getScopeId()
            )
        );

        if ($webEventTracking->isActive()) {
            return $webEventTracking->getCodeSnippet();
        }

        return null;
    }

    private function findFacebookPixelSnippet(): ?string
    {
        $facebookPixelSettings = FacebookPixel::createFromRepository(
            $this->repository->getFacebookPixelSnippet(
                $this->magentoStore->getCurrentScope()->getScopeId()
            )
        );

        if ($facebookPixelSettings->isActive()) {
            return $facebookPixelSettings->getCodeSnippet();
        }

        return null;
    }

    private function findFacebookAdsPixelSnippet(): ?string
    {
        $facebookPixelSettings = FacebookAdsPixel::createFromRepository(
            $this->repository->getFacebookAdsPixelSnippet(
                $this->magentoStore->getCurrentScope()->getScopeId()
            )
        );

        if ($facebookPixelSettings->isActive()) {
            return $facebookPixelSettings->getCodeSnippet();
        }

        return null;
    }

    private function findFacebookBusinessExtensionSnippet(): ?string
    {
        $facebookBusinessExtension = FacebookBusinessExtension::createFromRepository(
            $this->repository->getFacebookBusinessExtensionSnippet(
                $this->magentoStore->getCurrentScope()->getScopeId()
            )
        );

        if ($facebookBusinessExtension->isActive()) {
            return $facebookBusinessExtension->getCodeSnippet();
        }

        return null;
    }
}
