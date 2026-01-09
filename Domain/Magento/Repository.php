<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class Repository
{
    private $scopeConfig;
    private $configWriter;
    private $cacheManager;
    private $serializer;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        Manager $cacheManager,
        SerializerInterface $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
        $this->serializer = $serializer;
    }

    public function saveWebEventTracking(WebEventTracking $webEventTracking, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_WEB_EVENT_TRACKING,
            $this->serializer->serialize($webEventTracking->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function saveFacebookPixelSnippet(FacebookPixel $facebookPixelSettings, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_FACEBOOK_PIXEL_SNIPPET,
            $this->serializer->serialize($facebookPixelSettings->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function saveFacebookAdsPixelSnippet(FacebookAdsPixel $facebookAdsPixelSettings, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_FACEBOOK_ADS_PIXEL_SNIPPET,
            $this->serializer->serialize($facebookAdsPixelSettings->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function saveFacebookBusinessExtensionSnippet(FacebookBusinessExtension $facebookBusinessExtension, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_FACEBOOK_BUSINESS_EXTENSION_SNIPPET,
            $this->serializer->serialize($facebookBusinessExtension->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function getWebEventTracking($scopeId): array
    {
        $data = $this->scopeConfig->getValue(
            Config::CONFIG_DATA_WEB_EVENT_TRACKING,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        if (empty($data)) {
            return [];
        }
        return $this->serializer->unserialize($data);
    }

    public function getFacebookPixelSnippet($scopeId): array
    {
        $data = $this->scopeConfig->getValue(
            Config::CONFIG_DATA_FACEBOOK_PIXEL_SNIPPET,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        if (empty($data)) {
            return [];
        }
        return $this->serializer->unserialize($data);
    }

    public function getFacebookAdsPixelSnippet($scopeId): array
    {
        $data = $this->scopeConfig->getValue(
            Config::CONFIG_DATA_FACEBOOK_ADS_PIXEL_SNIPPET,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        if (empty($data)) {
            return [];
        }
        return $this->serializer->unserialize($data);
    }

    public function getFacebookBusinessExtensionSnippet($scopeId): array
    {
        $data = $this->scopeConfig->getValue(
            Config::CONFIG_DATA_FACEBOOK_BUSINESS_EXTENSION_SNIPPET,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        if (empty($data)) {
            return [];
        }
        return $this->serializer->unserialize($data);
    }

    public function getLiveSynchronization($scopeId)
    {
        $data = $this->scopeConfig->getValue(
            Config::CONFIG_LIVE_SYNCHRONIZATION,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        if (empty($data)) {
            return [];
        }
        return $this->serializer->unserialize($data);
    }

    public function saveLiveSynchronization(LiveSynchronization $liveSynchronization, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_LIVE_SYNCHRONIZATION,
            $this->serializer->serialize($liveSynchronization->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function saveWebformSettings(WebForm $webform, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_WEBFORMS_SETTINGS,
            $this->serializer->serialize($webform->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function getWebformSettings($scopeId): array
    {
        $data = $this->scopeConfig->getValue(
            Config::CONFIG_DATA_WEBFORMS_SETTINGS,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        if (empty($data)) {
            return [];
        }

        return $this->serializer->unserialize($data);
    }

    public function clearConfiguration($scopeId): void
    {
        $keys = [
            Config::CONFIG_DATA_FACEBOOK_PIXEL_SNIPPET,
            Config::CONFIG_DATA_FACEBOOK_ADS_PIXEL_SNIPPET,
            Config::CONFIG_DATA_FACEBOOK_BUSINESS_EXTENSION_SNIPPET,
            Config::CONFIG_DATA_WEBFORMS_SETTINGS,
            Config::CONFIG_DATA_WEB_EVENT_TRACKING,
            Config::CONFIG_LIVE_SYNCHRONIZATION
        ];

        foreach ($keys as $key) {
            $this->configWriter->delete($key, $this->getScope($scopeId), $this->getScopeId($scopeId));
        }

        $this->configWriter->delete('getresponse/plugin-mode', $this->getScope(null), $this->getScopeId(null));
        $this->cacheManager->clean(['config']);
    }

    private function getScope($scopeId): string
    {
        return $scopeId === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES;
    }

    private function getScopeId($scopeId): int
    {
        return (int) ($scopeId ?? Store::DEFAULT_STORE_ID);
    }
}
