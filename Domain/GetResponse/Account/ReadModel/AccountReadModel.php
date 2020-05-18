<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Account\ReadModel;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\Account;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\AccountFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;

class AccountReadModel
{
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getConnectionSettings(Scope $scope): ConnectionSettings
    {
        return ConnectionSettingsFactory::createFromArray(
            $this->repository->getConnectionSettings($scope->getScopeId())
        );
    }

    public function getAccount(Scope $scope): Account
    {
        return AccountFactory::createFromArray(
            $this->repository->getAccountInfo($scope->getScopeId())
        );
    }

    public function isConnected(Scope $scope): bool
    {
        $settings = $this->repository->getConnectionSettings($scope->getScopeId());
        return !empty($settings['apiKey']);
    }

    public function getHiddenApiKey(Scope $scope): string
    {
        $connectionSettings = $this->getConnectionSettings($scope);

        return str_repeat(
            "*",
            strlen($connectionSettings->getApiKey()) - 6
        ) . substr(
            $connectionSettings->getApiKey(),
            -6
        );
    }
}
