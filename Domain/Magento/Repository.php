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

class Repository
{
    private $scopeConfig;
    private $configWriter;
    private $cacheManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        Manager $cacheManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
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

        return json_decode($data, true);
    }

    public function saveConnectionSettings(ConnectionSettings $settings, $scopeId)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_CONNECTION_SETTINGS,
            json_encode($settings->toArray()),
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

        return (array) json_decode($data, true);
    }

    public function saveWebEventTracking(
        WebEventTrackingSettings $webEventTracking,
        $scopeId
    ) {
        $this->configWriter->save(
            Config::CONFIG_DATA_WEB_EVENT_TRACKING,
            json_encode($webEventTracking->toArray()),
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
        return json_decode($data, true);
    }

    public function saveShopStatus($status, $scopeId)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_SHOP_STATUS,
            $status,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function saveShopId($shopId, $scopeId)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_SHOP_ID,
            $shopId,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function saveEcommerceListId($listId, $scopeId)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_ECOMMERCE_LIST_ID,
            $listId,
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function saveAccountDetails(Account $account, $scopeId)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_ACCOUNT,
            json_encode($account->toArray()),
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

        return json_decode($data, true);
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

        return json_decode($data, true);
    }

    public function saveRegistrationSettings(
        SubscribeViaRegistration $registrationSettings,
        $scopeId
    ) {
        $this->configWriter->save(
            Config::CONFIG_DATA_REGISTRATION_SETTINGS,
            json_encode($registrationSettings->toArray()),
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
            json_encode($newsletterSettings->toArray()),
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
        return json_decode($data, true);
    }

    public function setCustomsOnInit(array $data, $scopeId)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_REGISTRATION_CUSTOMS,
            json_encode($data),
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
            json_encode($customFieldsMappingCollection->toArray()),
            $this->getScope($scopeId),
            $this->getScopeId($scopeId)
        );

        $this->cacheManager->clean(['config']);
    }

    public function saveWebformSettings(WebformSettings $webform, $scopeId)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_WEBFORMS_SETTINGS,
            json_encode($webform->toArray()),
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
        return json_decode($data, true);
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
        return (string) ($scopeId === null ? Store::DEFAULT_STORE_ID : $scopeId);
    }
}
