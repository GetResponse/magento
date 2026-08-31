<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Repository;

use GetResponse\GetResponseIntegration\Api\Data\NewsletterSubscriberInterface;
use GetResponse\GetResponseIntegration\Api\Data\NewsletterSubscriberSearchResultsInterface;
use GetResponse\GetResponseIntegration\Api\SubscriberRepositoryInterface;
use GetResponse\GetResponseIntegration\Model\Data\NewsletterSubscriber;
use GetResponse\GetResponseIntegration\Model\NewsletterSubscriberSearchResults;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\InputException;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Newsletter\Model\Subscriber as SubscriberModel;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

class SubscriberRepository implements SubscriberRepositoryInterface
{
    private const DEFAULT_PAGE_SIZE = 100;
    private const MAX_PAGE_SIZE = 100;
    private const DEFAULT_CURRENT_PAGE = 1;

    /** @var CollectionFactory */
    private $subscriberCollectionFactory;
    /** @var CollectionProcessorInterface */
    private $collectionProcessor;
    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param CollectionFactory $subscriberCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CollectionFactory $subscriberCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getListWithoutCustomers(
        int $storeId,
        SearchCriteriaInterface $searchCriteria
    ): NewsletterSubscriberSearchResultsInterface {
        $this->validateFilters($searchCriteria);
        $this->normalizePagination($searchCriteria);

        $subscribers = $this->subscriberCollectionFactory->create();
        $subscribers->addFilterToMap(
            NewsletterSubscriberInterface::EMAIL,
            'main_table.subscriber_email'
        );
        $store = $this->storeManager->getStore($storeId);
        /** @var Website $website */
        $website = $this->storeManager->getWebsite((int) $store->getWebsiteId());
        $subscribers->addStoreFilter($website->getStoreIds());
        $subscribers->addFieldToFilter('main_table.customer_id', 0);
        $subscribers->useOnlySubscribed();
        $subscribers->addFieldToSelect([
            'subscriber_id',
            'subscriber_email',
        ]);

        $this->collectionProcessor->process($searchCriteria, $subscribers);
        $subscribers->setOrder('main_table.subscriber_id', Collection::SORT_ORDER_ASC);

        $searchResults = new NewsletterSubscriberSearchResults();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount((int) $subscribers->getSize());

        $items = [];
        /** @var SubscriberModel $subscriber */
        foreach ($subscribers as $subscriber) {
            $item = new NewsletterSubscriber();
            $item->setId((int) $subscriber->getId());
            $item->setEmail((string) $subscriber->getEmail());
            $items[] = $item;
        }

        $searchResults->setItems($items);

        return $searchResults;
    }

    /**
     * Validate requested filters.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return void
     * @throws InputException
     */
    private function validateFilters(SearchCriteriaInterface $searchCriteria): void
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() !== NewsletterSubscriberInterface::EMAIL) {
                    throw new InputException(
                        __('Filtering by "%1" is not supported. Only "email" can be filtered.', $filter->getField())
                    );
                }
            }
        }
    }

    /**
     * Normalize pagination values.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return void
     */
    private function normalizePagination(SearchCriteriaInterface $searchCriteria): void
    {
        $pageSize = min(
            max((int) ($searchCriteria->getPageSize() ?: self::DEFAULT_PAGE_SIZE), 1),
            self::MAX_PAGE_SIZE
        );

        $currentPage = max(
            (int) $searchCriteria->getCurrentPage(),
            self::DEFAULT_CURRENT_PAGE
        );

        $searchCriteria->setPageSize($pageSize);
        $searchCriteria->setCurrentPage($currentPage);
    }
}
