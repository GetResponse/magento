<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Extensions;

use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Newsletter\Model\Subscriber as SubscriberModel;

class Customer
{
    private $extensionFactory;
    private $subscriberResource;

    public function __construct(
        CustomerExtensionFactory $extensionFactory,
        Subscriber $subscriberResource
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->subscriberResource = $subscriberResource;
    }

    public function afterGetExtensionAttributes(
        CustomerInterface $customer,
        ?CustomerExtensionInterface $extension = null
    ): ?CustomerExtensionInterface {
        if (null === $extension) {
            $extension = $this->extensionFactory->create();
        }

        if (false === $customer->getEmail()) {
            return $extension;
        }

        $subscriber = $this->subscriberResource->loadByEmail($customer->getEmail());

        $subscriberStatus = !empty($subscriber['subscriber_status']) ? (int)$subscriber['subscriber_status'] : 0;
        $isSubscribed = $subscriberStatus === SubscriberModel::STATUS_SUBSCRIBED;
        $extension->setIsSubscribed($isSubscribed);
        $customer->setExtensionAttributes($extension);

        return $extension;
    }
}
