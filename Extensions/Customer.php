<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Extensions;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
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

        $extension->setIsSubscribed(
            (bool) $this->subscriberFactory->create()->loadByEmail($customer->getEmail())->isSubscribed()
        );
        $customer->setExtensionAttributes($extension);

        return $extension;
    }
}
