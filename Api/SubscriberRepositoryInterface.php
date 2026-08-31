<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Api\Data\NewsletterSubscriberSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;

/**
 * @api
 */
interface SubscriberRepositoryInterface
{
    /**
     * Get active subscribers without associated customers from the website associated with a given store.
     *
     * @param int $storeId
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return NewsletterSubscriberSearchResultsInterface
     * @throws InputException
     */
    public function getListWithoutCustomers(
        int $storeId,
        SearchCriteriaInterface $searchCriteria
    ): NewsletterSubscriberSearchResultsInterface;
}
