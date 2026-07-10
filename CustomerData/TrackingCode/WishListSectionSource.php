<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData\TrackingCode;

use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\TrackingCodeBufferService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Customer\CustomerData\SectionSourceInterface;

class WishListSectionSource implements SectionSourceInterface
{
    /** @var TrackingCodeBufferService */
    private $session;
    /** @var Repository */
    private $repository;
    /** @var MagentoStore */
    private $magentoStore;

    /**
     * @param TrackingCodeBufferService $session
     * @param Repository $repository
     * @param MagentoStore $magentoStore
     */
    public function __construct(TrackingCodeBufferService $session, Repository $repository, MagentoStore $magentoStore)
    {
        $this->session = $session;
        $this->repository = $repository;
        $this->magentoStore = $magentoStore;
    }

    /**
     * Get section data.
     */
    public function getSectionData(): array
    {
        return [
            'getResponseShopId' => $this->getGetresponseShopId(),
            'productIdAddedToWishList' => $this->session->pullProductIdAddedToWishList(),
            'productIdRemovedFromWishList' => $this->session->pullProductIdRemovedFromWishList(),
        ];
    }

    /**
     * Get getresponse shop id.
     */
    private function getGetresponseShopId(): ?string
    {
        $scopeId = $this->magentoStore->getCurrentScope()->getScopeId();
        $webEventTracking = WebEventTracking::createFromRepository($this->repository->getWebEventTracking($scopeId));

        if (!$webEventTracking->isFeatureTrackingEnabled()) {
            return null;
        }

        return $webEventTracking->getGetresponseShopId();
    }
}
