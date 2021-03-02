<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

use Magento\Newsletter\Model\ResourceModel\Subscriber\Collection;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Newsletter\Model\Subscriber as SubscriberModel;

/**
 * @api
 */
class SubscriberController extends ApiController
{
    const PAGE_SIZE = 100;
    const PAGE = 1;

    /**
     * @return array
     */
    public function list(): array
    {
        $collection = [];
        $pageSize = (int) ($this->request->getParam('pageSize') ?? self::PAGE_SIZE);
        $currentPage = (int) ($this->request->getParam('currentPage') ?? self::PAGE);
        $subscriberCollectionFactory = $this->_objectManager->get(CollectionFactory::class);
        /** @var Collection $subscribers */
        $subscribers = $subscriberCollectionFactory->create();
        $count = $subscribers->count();

        // magento API always returns data
        if (($pageSize * $currentPage - $pageSize) >= $count) {
            return $collection;
        }

        $subscribers = $subscriberCollectionFactory->create();
        $subscribers->setPageSize($pageSize);
        $subscribers->setCurPage($currentPage);

        /** @var SubscriberModel $subscriber */
        foreach ($subscribers as $subscriber) {
            if ((int)$subscriber->getStatus() === 1) {
                $collection[] = $subscriber->toArray(['subscriber_email']);
            }
        }

        return $collection;
    }
}
