<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

use GetResponse\GetResponseIntegration\Api\Data\NewsletterSubscriberSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface SubscriberControllerInterface
{
    /**
     * Get active subscribers without associated customers from the website associated with a given store scope.
     *
     * @param string $scope
     * @param SearchCriteriaInterface $searchCriteria
     * @return NewsletterSubscriberSearchResultsInterface
     */
    public function getSubscribersWithoutCustomers(
        string $scope,
        SearchCriteriaInterface $searchCriteria
    ): NewsletterSubscriberSearchResultsInterface;

    /**
     * Handle unsubscribe.
     *
     * @param string $scope
     * @param string $email
     * @return void
     */
    public function unsubscribe(string $scope, string $email): void;
}
