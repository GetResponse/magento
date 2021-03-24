<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use \Magento\Customer\Model\Customer as MagentoCustomer;
use Magento\Newsletter\Model\Subscriber;

class CustomerFactory
{
    private $magentoCustomer;
    private $subscriber;
    private $addressFactory;

    public function __construct(
        MagentoCustomer $magentoCustomer,
        Subscriber $subscriber,
        AddressFactory $addressFactory
    ) {
        $this->magentoCustomer = $magentoCustomer;
        $this->subscriber = $subscriber;
        $this->addressFactory = $addressFactory;
    }

    public function create(int $id): Customer
    {
        $customer = $this->magentoCustomer->load($id);
        $subscriber = $this->subscriber->loadByCustomerId($id);
        $isSubscribed = $subscriber->isSubscribed();

        $billingAddress = null !== $customer->getDefaultBillingAddress() ? $this->addressFactory->create($customer->getDefaultBillingAddress()) : [];
        $shippingAddress = null !== $customer->getDefaultShippingAddress() ? $this->addressFactory->create($customer->getDefaultShippingAddress()) : [];

        return new Customer(
            (int)$customer->getId(),
            $customer->getEmail(),
            $customer->getFirstname(),
            $customer->getLastname(),
            $isSubscribed,
            $shippingAddress,
            [],
            array_merge(
                $billingAddress->toCustomFieldsArray('billing'),
                $shippingAddress->toCustomFieldsArray('shipping')
            )
        );
    }
}
