<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Ecommerce\ReadModel;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;

class EcommerceReadModel
{
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getListId(Scope $scope)
    {
        return $this->repository->getEcommerceListId($scope->getScopeId());
    }

    public function getShopId(Scope $scope)
    {
        return $this->repository->getShopId($scope->getScopeId());
    }

    public function getShopStatus(Scope $scope): string
    {
        return $this->repository->getShopStatus($scope->getScopeId());
    }
}
