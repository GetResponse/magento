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
    /** @var ScopeConfigInterface */
    private $scopeConfig;
    /** @var WriterInterface */
    private $configWriter;
    /** @var Manager */
    private $cacheManager;
    /** @var SerializerInterface */
    private $serializer;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param Manager $cacheManager
     * @param SerializerInterface $serializer
     */
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

    /**
     * Handle save web event tracking.
     *
     * @param WebEventTracking $webEventTracking
     * @param mixed $scopeId
     */
    public function saveWebEventTracking(WebEventTracking $webEventTracking, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_WEB_EVENT_TRACKING,
            $this->serializer->serialize($webEventTracking->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    /**
     * Handle save facebook pixel snippet.
     *
     * @param FacebookPixel $facebookPixelSettings
     * @param mixed $scopeId
     */
    public function saveFacebookPixelSnippet(FacebookPixel $facebookPixelSettings, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_FACEBOOK_PIXEL_SNIPPET,
            $this->serializer->serialize($facebookPixelSettings->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    /**
     * Handle save facebook ads pixel snippet.
     *
     * @param FacebookAdsPixel $facebookAdsPixelSettings
     * @param mixed $scopeId
     */
    public function saveFacebookAdsPixelSnippet(FacebookAdsPixel $facebookAdsPixelSettings, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_FACEBOOK_ADS_PIXEL_SNIPPET,
            $this->serializer->serialize($facebookAdsPixelSettings->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    /**
     * Handle save facebook business extension snippet.
     *
     * @param FacebookBusinessExtension $facebookBusinessExtension
     * @param mixed $scopeId
     */
    public function saveFacebookBusinessExtensionSnippet(
        FacebookBusinessExtension $facebookBusinessExtension,
        $scopeId
    ): void {
        $this->configWriter->save(
            Config::CONFIG_DATA_FACEBOOK_BUSINESS_EXTENSION_SNIPPET,
            $this->serializer->serialize($facebookBusinessExtension->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    /**
     * Get web event tracking.
     *
     * @param mixed $scopeId
     */
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

    /**
     * Get facebook pixel snippet.
     *
     * @param mixed $scopeId
     */
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

    /**
     * Get facebook ads pixel snippet.
     *
     * @param mixed $scopeId
     */
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

    /**
     * Get facebook business extension snippet.
     *
     * @param mixed $scopeId
     */
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

    /**
     * Get live synchronization.
     *
     * @param mixed $scopeId
     */
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

    /**
     * Handle save live synchronization.
     *
     * @param LiveSynchronization $liveSynchronization
     * @param mixed $scopeId
     */
    public function saveLiveSynchronization(LiveSynchronization $liveSynchronization, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_LIVE_SYNCHRONIZATION,
            $this->serializer->serialize($liveSynchronization->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    /**
     * Handle save webform settings.
     *
     * @param WebForm $webform
     * @param mixed $scopeId
     */
    public function saveWebformSettings(WebForm $webform, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_WEBFORMS_SETTINGS,
            $this->serializer->serialize($webform->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    /**
     * Get webform settings.
     *
     * @param mixed $scopeId
     */
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

    /**
     * Handle clear configuration.
     *
     * @param mixed $scopeId
     */
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

    /**
     * Get scope.
     *
     * @param mixed $scopeId
     */
    private function getScope($scopeId): string
    {
        return $scopeId === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES;
    }

    /**
     * Get scope id.
     *
     * @param mixed $scopeId
     */
    private function getScopeId($scopeId): int
    {
        return (int) ($scopeId ?? Store::DEFAULT_STORE_ID);
    }
}
