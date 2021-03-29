<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Extensions;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Newsletter\Model\Subscriber;

class Customer
{
    private $extensionFactory;
    private $subscriberModel;

    public function __construct(
        CustomerExtensionFactory $extensionFactory,
        Subscriber $subscriberModel
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->subscriberModel = $subscriberModel;
    }

    public function afterGetExtensionAttributes(
        CustomerInterface $customer,
        ?CustomerExtensionInterface $extension = null
    ): ?CustomerExtensionInterface {

        if (null === $extension) {
            $extension = $this->extensionFactory->create();
        }

        $subscriber = $this->subscriberModel->loadByEmail($customer->getEmail());

        $extension->setIsSubscribed($subscriber->isSubscribed());
        $customer->setExtensionAttributes($extension);

        return $extension;
    }
}
