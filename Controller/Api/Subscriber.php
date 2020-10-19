<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Newsletter\Model\ResourceModel\Subscriber\Collection;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Newsletter\Model\Subscriber as SubscriberModel;

/**
 * @api
 */
class Subscriber
{
    const PAGE_SIZE = 100;
    const PAGE = 1;

    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }


    /**
     * @return array
     */
    public function list(): array
    {
        $collection = [];
        /** @var RequestInterface $request */
        $request = $this->objectManager->get(RequestInterface::class);
        $pageSize = (int) ($request->getParam('pageSize') ?? self::PAGE_SIZE);
        $currentPage = (int) ($request->getParam('currentPage') ?? self::PAGE);
        $subscriberCollectionFactory = $this->objectManager->get(CollectionFactory::class);
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
