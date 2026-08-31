<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Search results for newsletter subscribers.
 *
 * @api
 */
interface NewsletterSubscriberSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get subscribers.
     *
     * @return \GetResponse\GetResponseIntegration\Api\Data\NewsletterSubscriberInterface[]
     */
    public function getItems();

    /**
     * Set subscribers.
     *
     * @param \GetResponse\GetResponseIntegration\Api\Data\NewsletterSubscriberInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
