<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

abstract class ScopeReadModel
{
    protected function getScopeName(Scope $scope): string
    {
        return $scope->getScopeId() === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_WEBSITES;
    }

    protected function getScopeId(Scope $scope): string
    {
        return (string) ($scope->getScopeId() === null ? Store::DEFAULT_STORE_ID : $scope->getScopeId());
    }
}
