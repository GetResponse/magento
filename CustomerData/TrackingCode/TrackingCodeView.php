<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData\TrackingCode;

use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use Magento\Framework\DataObject\IdentityInterface as Subject;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;

abstract class TrackingCodeView
{
    protected $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    abstract protected function getBlockName(): string;

    protected function isAllowed(Subject $subject, int $scopeId): bool
    {
        $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());
        $webEventTracking = WebEventTracking::createFromRepository($this->repository->getWebEventTracking($scopeId));

        return
            $pluginMode->isNewVersion()
            && $webEventTracking->isFeatureTrackingEnabled()
            && $subject->getNameInLayout() === $this->getBlockName();
    }

    protected function getGetresponseShopId(int $scopeId): ?string
    {
        $webEventTracking = WebEventTracking::createFromRepository($this->repository->getWebEventTracking($scopeId));

        return $webEventTracking->isFeatureTrackingEnabled() ? $webEventTracking->getGetresponseShopId() : null;
    }
}
