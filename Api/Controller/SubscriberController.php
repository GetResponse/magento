<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api\Controller;

use GetResponse\GetResponseIntegration\Application\Magento\Newsletter\NewsletterUnsubscribeService;
use GetResponse\GetResponseIntegration\Controller\Api\SubscriberControllerInterface;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Newsletter\Model\Subscriber as SubscriberModel;

/**
 * @api
 */
class SubscriberController extends ApiController implements SubscriberControllerInterface
{
    /** @var CollectionFactory */
    private $subscriberCollectionFactory;
    /** @var NewsletterUnsubscribeService */
    private $newsletterUnsubscribeService;

    /**
     * @param Repository $repository
     * @param MagentoStore $magentoStore
     * @param CollectionFactory $subscriberCollectionFactory
     * @param NewsletterUnsubscribeService $newsletterUnsubscribeService
     */
    public function __construct(
        Repository $repository,
        MagentoStore $magentoStore,
        CollectionFactory $subscriberCollectionFactory,
        NewsletterUnsubscribeService $newsletterUnsubscribeService
    ) {
        parent::__construct($repository, $magentoStore);
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->newsletterUnsubscribeService = $newsletterUnsubscribeService;
    }

    /**
     * Handle list.
     *
     * @param int $pageSize
     * @param int $currentPage
     */
    public function list(int $pageSize, int $currentPage): array
    {
        $collection = [];

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

    /**
     * Handle unsubscribe.
     *
     * @param string $scope
     * @param string $email
     */
    public function unsubscribe(string $scope, string $email): void
    {
        $scope = (int) $scope;
        $this->verifyScope($scope);

        $this->newsletterUnsubscribeService->unsubscribe($email, $scope);
    }
}
