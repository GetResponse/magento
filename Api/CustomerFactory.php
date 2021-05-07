<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Customer\Model\Customer as MagentoCustomer;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Model\Order as MagentoOrder;

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
        $isSubscribed = $this->isCustomerSubscriber($id);

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

    public function createFromOrder(MagentoOrder $order): Customer
    {
        $customerId = null === $order->getCustomerId() ? null : (int)$order->getCustomerId();

        $isSubscribed = $this->isCustomerSubscriber($customerId);

        $billingAddress = $this->addressFactory->create($order->getBillingAddress());
        $shippingAddress = $this->addressFactory->create($order->getShippingAddress());

        $customFields = [
            'group_id' => $order->getCustomerGroupId(),
            'store_id' => $order->getStoreId(),
            'prefix' => $order->getCustomerPrefix(),
            'dob' => $order->getCustomerDob(),
            'tax_vat' => $order->getCustomerTaxvat(),
            'gender' => $order->getCustomerGender(),
            'middlename' => $order->getCustomerMiddlename(),
        ];

        return new Customer(
            $customerId,
            $order->getCustomerEmail(),
            $order->getCustomerFirstname(),
            $order->getCustomerLastname(),
            $isSubscribed,
            $this->addressFactory->create($order->getBillingAddress()),
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

    private function isCustomerSubscriber(?int $customerId): bool
    {
        if (null === $customerId) {
            return false;
        }

        $subscriber = $this->subscriber->loadByCustomerId($customerId);

        return $subscriber->isSubscribed();
    }
}
