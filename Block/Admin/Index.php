<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\Magento\FacebookAdsPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\Magento\WebForm;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Index extends Template
{
    private MagentoStore $magentoStore;
    private Repository $repository;

    public function __construct(
        Context $context,
        MagentoStore $magentoStore,
        Repository $repository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->magentoStore = $magentoStore;
        $this->repository = $repository;
    }

    public function getStoreStatuses(): array
    {
        $stores = $this->magentoStore->getMagentoStores();
        $statuses = [];

        foreach ($stores as $storeId => $storeName) {
            $statuses[$storeId] = [
                'name' => $storeName,
                'live_sync_enabled' => $this->isLiveSyncEnabled($storeId),
                'web_tracking_enabled' => $this->isWebTrackingEnabled($storeId),
                'facebook_pixel_enabled' => $this->isFacebookPixelEnabled($storeId),
                'facebook_ads_pixel_enabled' => $this->isFacebookAdsPixelEnabled($storeId),
                'webforms_enabled' => $this->isWebFormsEnabled($storeId)
            ];
        }

        return $statuses;
    }

    private function isLiveSyncEnabled(int $storeId): bool
    {
        $liveSyncData = $this->repository->getLiveSynchronization($storeId);
        $liveSync = LiveSynchronization::createFromRepository($liveSyncData);

        return $liveSync->isActive();
    }

    private function isWebTrackingEnabled(int $storeId): bool
    {
        $webTrackingData = $this->repository->getWebEventTracking($storeId);
        $webTracking = WebEventTracking::createFromRepository($webTrackingData);

        return $webTracking->isActive();
    }

    private function isFacebookPixelEnabled(int $storeId): bool
    {
        $facebookPixelData = $this->repository->getFacebookPixelSnippet($storeId);
        $facebookPixel = FacebookPixel::createFromRepository($facebookPixelData);

        return $facebookPixel->isActive();
    }

    private function isFacebookAdsPixelEnabled(int $storeId): bool
    {
        $facebookAdsPixelData = $this->repository->getFacebookAdsPixelSnippet($storeId);
        $facebookAdsPixel = FacebookAdsPixel::createFromRepository($facebookAdsPixelData);

        return $facebookAdsPixel->isActive();
    }

    private function isWebFormsEnabled(int $storeId): bool
    {
        $webFormData = $this->repository->getWebformSettings($storeId);
        $webForm = WebForm::createFromRepository($webFormData);

        return $webForm->isEnabled();
    }
}
