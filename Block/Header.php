<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block;

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
            'trackingCodeSnippet' => $this->getTrackingCodeSnippet(),
            'facebookPixelCodeSnippet' => $this->getFacebookPixelSnippet()
        ];
    }

    private function getTrackingCodeSnippet(): string
    {
        $webEventTracking = WebEventTracking::createFromRepository(
            $this->repository->getWebEventTracking(
                $this->magentoStore->getCurrentScope()->getScopeId()
            )
        );

        if ($webEventTracking->isEnabled()) {
            return $webEventTracking->getCodeSnippet();
        }

        return '';
    }

    private function getFacebookPixelSnippet(): string
    {
        $facebookPixelSettings = FacebookPixel::createFromRepository(
            $this->repository->getFacebookPixelSnippet(
                $this->magentoStore->getCurrentScope()->getScopeId()
            )
        );

        if ($facebookPixelSettings->isActive()) {
            return $facebookPixelSettings->getCodeSnippet();
        }

        return '';
    }
}
