<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Application\Magento\Newsletter;

use Magento\Newsletter\Model\ResourceModel\Subscriber as SubscriberResource;
use Magento\Newsletter\Model\Subscriber as SubscriberModel;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;

class NewsletterUnsubscribeService
{
    /** @var SubscriberResource */
    private $subscriberResource;
    /** @var SubscriberFactory */
    private $subscriberFactory;
    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param SubscriberResource $subscriberResource
     * @param SubscriberFactory $subscriberFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SubscriberResource $subscriberResource,
        SubscriberFactory $subscriberFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->subscriberResource = $subscriberResource;
        $this->subscriberFactory = $subscriberFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Handle unsubscribe.
     *
     * @param string $email
     * @param int $storeId
     */
    public function unsubscribe(string $email, int $storeId): void
    {
        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();

        /** @var SubscriberModel $subscriber */
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadBySubscriberEmail($email, $websiteId);

        if (empty($subscriber->getId())
            || (int)$subscriber->getSubscriberStatus() === SubscriberModel::STATUS_UNSUBSCRIBED
        ) {
            return;
        }

        $subscriber->setSubscriberStatus(SubscriberModel::STATUS_UNSUBSCRIBED);

        $this->subscriberResource->save($subscriber);
    }
}
