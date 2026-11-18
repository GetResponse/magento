<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

use GetResponse\GetResponseIntegration\Api\ProductAvailabilityRepositoryInterface;

interface ProductAvailabilityControllerInterface
{
    /**
     * Get product availability (salability) for a given store scope.
     *
     * @param int $scope Store view ID
     * @param int[] $productIds
     * @param int $pageSize
     * @param int $currentPage
     * @return \GetResponse\GetResponseIntegration\Api\ProductAvailability[]
     */
    public function getList(
        int $scope,
        array $productIds = [],
        int $pageSize = ProductAvailabilityRepositoryInterface::MAX_PAGE_SIZE,
        int $currentPage = 1
    ): array;
}
