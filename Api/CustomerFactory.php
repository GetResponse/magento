<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Customer\Model\Customer as MagentoCustomer;
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

        $billingAddress = $this->addressFactory->create($customer->getDefaultBillingAddress());
        $shippingAddress = $this->addressFactory->create($customer->getDefaultShippingAddress());

        $customFields = [
            'website_id' => $customer->getWebsiteId(),
            'group_id' => $customer->getGroupId(),
            'store_id' => $customer->getStoreId(),
            'create_at' => $customer->getCreatedAtTimestamp(),
            'is_active' => $customer->getData('is_active'),
            'prefix' => $customer->getData('prefix'),
            'dob' => $customer->getData('dob'),
            'tax_vat' => $customer->getData('taxvat'),
            'gender' => $customer->getData('gender'),
            'middlename' => $customer->getData('middlename'),
        ];

        return new Customer(
            (int)$customer->getId(),
            $customer->getEmail(),
            $customer->getFirstname(),
            $customer->getLastname(),
            $isSubscribed,
            $billingAddress,
            [],
            array_merge(
                $customFields,
                null !== $billingAddress ? $billingAddress->toCustomFieldsArray('billing') : [],
                null !== $shippingAddress ? $shippingAddress->toCustomFieldsArray('shipping') : []
            )
        );
    }

    public function createFromSubscriber(Subscriber $subscriber): Customer
    {
        return new Customer(
            (int)$subscriber->getId(),
            $subscriber->getEmail(),
            '',
            '',
            $subscriber->isSubscribed(),
            null,
            [],
            []
        );
    }
}
