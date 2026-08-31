<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Model;

use GetResponse\GetResponseIntegration\Api\Data\NewsletterSubscriberSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class NewsletterSubscriberSearchResults extends SearchResults implements
    NewsletterSubscriberSearchResultsInterface
{
}
