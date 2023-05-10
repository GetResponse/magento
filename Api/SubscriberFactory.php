<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Newsletter\Model\Subscriber as MagentoSubscriber;

class SubscriberFactory
{
    public function create(MagentoSubscriber $magentoSubscriber): Subscriber
    {
        return new Subscriber(
            (int)$magentoSubscriber->getId(),
            $magentoSubscriber->getEmail(),
            $magentoSubscriber->getSubscriberFullName() !== null ? $magentoSubscriber->getSubscriberFullName() : '',
            $magentoSubscriber->isSubscribed(),
            [],
            [
                'store_id' => $magentoSubscriber->getStoreId()
            ]
        );
    }
}
