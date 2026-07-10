<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData\TrackingCode;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Helper\CspNonceProviderFactory;
use GetResponse\GetResponseIntegration\Helper\NullCspNonceProvider;
use Magento\Framework\DataObject\IdentityInterface as Subject;
use Magento\Framework\View\Element\AbstractBlock;

abstract class TrackingCodeView
{
    /** @var Repository */
    protected $repository;
    /** @var mixed */
    protected $cspNonceProvider;

    /**
     * @param Repository $repository
     * @param ?CspNonceProviderFactory $cspNonceProviderFactory
     */
    public function __construct(Repository $repository, ?CspNonceProviderFactory $cspNonceProviderFactory)
    {
        $this->repository = $repository;
        $this->cspNonceProvider = $cspNonceProviderFactory->create() ?? new NullCspNonceProvider();
    }

    /**
     * Get block name.
     */
    abstract protected function getBlockName(): string;

    /**
     * Check allowed.
     *
     * @param Subject $subject
     * @param int $scopeId
     */
    protected function isAllowed(Subject $subject, int $scopeId): bool
    {
        $webEventTracking = WebEventTracking::createFromRepository($this->repository->getWebEventTracking($scopeId));

        /** @var AbstractBlock $subject */
        return $webEventTracking->isFeatureTrackingEnabled() && $subject->getNameInLayout() === $this->getBlockName();
    }

    /**
     * Get getresponse shop id.
     *
     * @param int $scopeId
     */
    protected function getGetresponseShopId(int $scopeId): ?string
    {
        $webEventTracking = WebEventTracking::createFromRepository($this->repository->getWebEventTracking($scopeId));

        return $webEventTracking->isFeatureTrackingEnabled() ? $webEventTracking->getGetresponseShopId() : null;
    }
}
