<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CatalogRuleRepositoryInterface
{
    /**
     * Get list of catalog rules.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return CatalogRule[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array;
}
