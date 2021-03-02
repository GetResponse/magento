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
            'trackingCodeSnippet' => $this->findTrackingCodeSnippet(),
            'facebookPixelCodeSnippet' => $this->findFacebookPixelSnippet()
        ];
    }

    /**
     * @return string|null
     */
    private function findTrackingCodeSnippet()
    {
        $webEventTracking = WebEventTracking::createFromRepository(
            $this->repository->getWebEventTracking(
                $this->magentoStore->getCurrentScope()->getScopeId()
            )
        );

        if ($webEventTracking->isEnabled()) {
            return $webEventTracking->getCodeSnippet();
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function findFacebookPixelSnippet()
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
}
