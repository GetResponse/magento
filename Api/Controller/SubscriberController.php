<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api\Controller;

use GetResponse\GetResponseIntegration\Controller\Api\SubscriberControllerInterface;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Newsletter\Model\ResourceModel\Subscriber\Collection;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Newsletter\Model\Subscriber as SubscriberModel;

/**
 * @api
 */
class SubscriberController extends ApiController implements SubscriberControllerInterface
{
    private $subscriberCollectionFactory;

    /**
     * @param Repository $repository
     * @param MagentoStore $magentoStore
     * @throws WebapiException
     */
    public function __construct(
        Repository $repository,
        MagentoStore $magentoStore,
        CollectionFactory $subscriberCollectionFactory
    ) {
        parent::__construct($repository, $magentoStore);
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->verifyPluginMode();
    }

    /**
     * @param int $pageSize
     * @param int $currentPage
     * @return mixed
     */
    public function list(int $pageSize, int $currentPage): array
    {
        $collection = [];

        /** @var Collection $subscribers */
        $subscribers = $this->subscriberCollectionFactory->create();
        $count = $subscribers->count();

        // magento API always returns data
        if (($pageSize * $currentPage - $pageSize) >= $count) {
            return $collection;
        }

        $subscribers = $this->subscriberCollectionFactory->create();
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
