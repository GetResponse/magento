<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Extensions;

use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Newsletter\Model\SubscriberFactory;

class Customer
{
    private $extensionFactory;
    private $subscriberFactory;

    public function __construct(
        CustomerExtensionFactory $extensionFactory,
        SubscriberFactory $subscriberFactory
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->subscriberFactory = $subscriberFactory;
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
        $websiteId = (int)$customer->getWebsiteId();
        $subscriber = $this->subscriberFactory->create()->loadBySubscriberEmail($customer->getEmail(), $websiteId);
        $extension->setIsSubscribed((bool)$subscriber->isSubscribed());
        $customer->setExtensionAttributes($extension);

        return $extension;
    }
}
