<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\Account;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistration;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Serialize\SerializerInterface;

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

    public function getShopId($scopeId)
    {
        return $this->scopeConfig->getValue(
            Config::CONFIG_DATA_SHOP_ID,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    public function getShopStatus($scopeId): string
    {
        $status = $this->scopeConfig->getValue(
            Config::CONFIG_DATA_SHOP_STATUS,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        return 'enabled' === $status ? 'enabled' : 'disabled';
    }

    public function getAccountInfo($scopeId): array
    {
        $data = $this->scopeConfig->getValue(
            Config::CONFIG_DATA_ACCOUNT,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        if (empty($data)) {
            return [];
        }

        return $this->serializer->unserialize($data);
    }

    public function saveConnectionSettings(ConnectionSettings $settings, $scopeId)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_CONNECTION_SETTINGS,
            $this->serializer->serialize($settings->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function getConnectionSettings($scopeId): array
    {
        $data = $this->scopeConfig->getValue(
            Config::CONFIG_DATA_CONNECTION_SETTINGS,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        if (empty($data)) {
            return [];
        }

        return $this->serializer->unserialize($data);
    }

    public function saveWebEventTracking(WebEventTracking $webEventTracking, $scopeId)
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

    public function getPluginMode(): ?string
    {
        $value = $this->scopeConfig->getValue(
            Config::CONFIG_DATA_PLUGIN_MODE
        );

        return is_null($value) ? null : (string)$value;
    }

    public function savePluginMode(PluginMode $pluginMode): void
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_PLUGIN_MODE,
            $pluginMode->getMode()
        );

        $this->cacheManager->clean(['config']);
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

    public function saveShopStatus($status, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_SHOP_STATUS,
            $status,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function saveShopId($shopId, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_SHOP_ID,
            $shopId,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function saveEcommerceListId($listId, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_ECOMMERCE_LIST_ID,
            $listId,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function saveAccountDetails(Account $account, $scopeId): void
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_ACCOUNT,
            $this->serializer->serialize($account->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function getRegistrationSettings($scopeId): array
    {
        $data = $this->scopeConfig->getValue(
            Config::CONFIG_DATA_REGISTRATION_SETTINGS,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        if (empty($data)) {
            return [];
        }

        return $this->serializer->unserialize($data);
    }

    public function getNewsletterSettings($scopeId): array
    {
        $data = $this->scopeConfig->getValue(
            Config::CONFIG_DATA_NEWSLETTER_SETTINGS,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        if (empty($data)) {
            return [];
        }

        return $this->serializer->unserialize($data);
    }

    public function saveRegistrationSettings(
        SubscribeViaRegistration $registrationSettings,
        $scopeId
    ) {
        $this->configWriter->save(
            Config::CONFIG_DATA_REGISTRATION_SETTINGS,
            $this->serializer->serialize($registrationSettings->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
        $this->cacheManager->clean(['config']);
    }

    public function saveNewsletterSettings(
        NewsletterSettings $newsletterSettings,
        $scopeId
    ) {
        $this->configWriter->save(
            Config::CONFIG_DATA_NEWSLETTER_SETTINGS,
            $this->serializer->serialize($newsletterSettings->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
        $this->cacheManager->clean(['config']);
    }

    public function getCustomFieldsMappingForRegistration($scopeId): array
    {
        $data = $this->scopeConfig->getValue(
            Config::CONFIG_DATA_REGISTRATION_CUSTOMS,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        if (empty($data)) {
            return [];
        }

        return $this->serializer->unserialize($data);
    }

    public function setCustomsOnInit(array $data, $scopeId)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_REGISTRATION_CUSTOMS,
            $this->serializer->serialize($data),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function updateCustoms(
        CustomFieldsMappingCollection $customFieldsMappingCollection,
        $scopeId
    ) {
        $this->configWriter->save(
            Config::CONFIG_DATA_REGISTRATION_CUSTOMS,
            $this->serializer->serialize($customFieldsMappingCollection->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function saveWebformSettings(WebForm $webform, $scopeId)
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

    public function clearDatabase($scopeId)
    {
        $this->clearConnectionSettings($scopeId);
        $this->clearRegistrationSettings($scopeId);
        $this->clearAccountDetails($scopeId);
        $this->clearWebforms($scopeId);
        $this->clearWebEventTracking($scopeId);
        $this->clearCustoms($scopeId);
        $this->clearEcommerceSettings($scopeId);
        $this->clearUnauthorizedApiCallDate($scopeId);
        $this->clearNewsletterSettings($scopeId);
        $this->clearCustomOrigin($scopeId);

        $this->cacheManager->clean(['config']);
    }

    public function clearConfiguration($scopeId): void
    {
        $keys = [
            Config::CONFIG_DATA_FACEBOOK_PIXEL_SNIPPET,
            Config::CONFIG_DATA_FACEBOOK_ADS_PIXEL_SNIPPET,
            Config::CONFIG_DATA_FACEBOOK_BUSINESS_EXTENSION_SNIPPET,
            Config::CONFIG_DATA_WEBFORMS_SETTINGS,
            Config::CONFIG_DATA_WEB_EVENT_TRACKING,
            Config::CONFIG_LIVE_SYNCHRONIZATION,
        ];

        foreach ($keys as $key) {
            $this->configWriter->delete($key, $this->getScope($scopeId), $this->getScopeId($scopeId));
        }

        $this->cacheManager->clean(['config']);
    }

    private function clearCustomOrigin($scopeId)
    {
        $this->configWriter->delete(
            Config::CONFIG_DATA_ORIGIN_CUSTOM_FIELD_ID,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    public function clearConnectionSettings($scopeId)
    {
        $this->configWriter->delete(
            Config::CONFIG_DATA_CONNECTION_SETTINGS,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    public function clearRegistrationSettings($scopeId)
    {
        $this->configWriter->delete(
            Config::CONFIG_DATA_REGISTRATION_SETTINGS,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function clearAccountDetails($scopeId)
    {
        $this->configWriter->delete(
            Config::CONFIG_DATA_ACCOUNT,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    public function clearWebforms($scopeId)
    {
        $this->configWriter->delete(
            Config::CONFIG_DATA_WEBFORMS_SETTINGS,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    public function clearNewsletterSettings($scopeId)
    {
        $this->configWriter->delete(
            Config::CONFIG_DATA_NEWSLETTER_SETTINGS,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function clearWebEventTracking($scopeId)
    {
        $this->configWriter->delete(
            Config::CONFIG_DATA_WEB_EVENT_TRACKING,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    public function clearCustoms($scopeId)
    {
        $this->configWriter->delete(
            Config::CONFIG_DATA_REGISTRATION_CUSTOMS,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    public function clearEcommerceSettings($scopeId)
    {
        $this->configWriter->delete(
            Config::CONFIG_DATA_SHOP_STATUS,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->configWriter->delete(
            Config::CONFIG_DATA_SHOP_ID,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->configWriter->delete(
            Config::CONFIG_DATA_ECOMMERCE_LIST_ID,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    private function clearUnauthorizedApiCallDate($scopeId)
    {
        $this->configWriter->delete(
            Config::INVALID_REQUEST_DATE_TIME,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    public function getEcommerceListId($scopeId)
    {
        return $this->scopeConfig->getValue(
            Config::CONFIG_DATA_ECOMMERCE_LIST_ID,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );
    }

    private function getScope($scopeId): string
    {
        return $scopeId === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_WEBSITES;
    }

    private function getScopeId($scopeId): string
    {
        return (string) ($scopeId ?? Store::DEFAULT_STORE_ID);
    }
}
