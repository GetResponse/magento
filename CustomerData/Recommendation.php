<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Recommendation\RecommendationSession;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Customer\CustomerData\SectionSourceInterface;

class Recommendation implements SectionSourceInterface
{
    private $session;
    /** @var Repository */
    private $repository;
    /** @var MagentoStore */
    private $magentoStore;

    public function __construct(RecommendationSession $session, Repository $repository, MagentoStore $magentoStore)
    {
        $this->session = $session;
        $this->repository = $repository;
        $this->magentoStore = $magentoStore;
    }

    public function getSectionData(): array
    {
        return [
            'getResponseShopId' => $this->getGetresponseShopId(),
            'productIdAddedToWishList' => $this->session->pullProductIdAddedToWishList(),
            'productIdRemovedFromWishList' => $this->session->pullProductIdRemovedFromWishList(),
        ];
    }

    private function getGetresponseShopId(): ?string
    {
        $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());
        if (!$pluginMode->isNewVersion()) {
            return null;
        }

        $scopeId = $this->magentoStore->getCurrentScope()->getScopeId();
        $webEventTracking = WebEventTracking::createFromRepository($this->repository->getWebEventTracking($scopeId));
        if (!$webEventTracking->isFeatureTrackingEnabled()) {
            return null;
        }

        return $webEventTracking->getGetresponseShopId();
    }
}
