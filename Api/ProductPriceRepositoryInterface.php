<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ProductPriceRepositoryInterface
{
    /**
     * Get product prices.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return ProductPrice[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array;
}
