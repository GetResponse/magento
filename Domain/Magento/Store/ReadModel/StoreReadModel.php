<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Store\ReadModel;

use GetResponse\GetResponseIntegration\Domain\Magento\ScopeReadModel;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class StoreReadModel extends ScopeReadModel
{
    private $scopeConfig;
    private $storeManager;
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function getStoreLanguage(Scope $scope): string
    {
        $countryCode = $this->scopeConfig->getValue(
            Config::CONFIG_LOCALE_CODE,
            $this->getScopeName($scope),
            $this->getScopeId($scope)
        );

        return substr($countryCode, 0, 2);
    }

    public function getStoreCurrency(Scope $scope): string
    {
        return $this->storeManager->getStore($scope->getScopeId())->getCurrentCurrencyCode();
    }

    public function getGetResponsePluginVersion(): string
    {
        $moduleInfo = $this->objectManager
            ->create(ModuleList::class)
            ->getOne('GetResponse_GetResponseIntegration');

        return isset($moduleInfo['setup_version']) ? $moduleInfo['setup_version'] : '';
    }
}
