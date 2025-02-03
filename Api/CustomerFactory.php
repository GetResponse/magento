<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Model\Order as MagentoOrder;

class CustomerFactory
{
    private $customerRepository;
    private $subscriber;
    private $addressFactory;
    private $subscriberCollectionFactory;


    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Subscriber $subscriber,
        AddressFactory $addressFactory,
        CollectionFactory $subscriberCollectionFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->subscriber = $subscriber;
        $this->addressFactory = $addressFactory;
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
    }

    public function create(CustomerInterface $customer): Customer
    {
        $isSubscribed = $this->isCustomerSubscribed($customer);

        $billingAddress = null;
        $shippingAddress = null;
        foreach ($customer->getAddresses() as $address) {
            if ($address->isDefaultBilling()) {
                $billingAddress = $this->addressFactory->createFromCustomer($address);
            }
            if ($address->isDefaultShipping()) {
                $shippingAddress = $this->addressFactory->createFromCustomer($address);
            }
        }

        $customFields = [
            'website_id' => $customer->getWebsiteId(),
            'group_id' => $customer->getGroupId(),
            'store_id' => $customer->getStoreId(),
            'create_at' => $customer->getCreatedAt(),
            'prefix' => $customer->getPrefix(),
            'sufix' => $customer->getSuffix(),
            'dob' => $customer->getDob(),
            'tax_vat' => $customer->getTaxvat(),
            'gender' => $customer->getGender(),
            'middlename' => $customer->getMiddlename(),
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
        $customerId = (int)$order->getCustomerId();
        $isSubscribed = false;

        $billingAddress = null;
        $shippingAddress = null;

        if (null !== $customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $isSubscribed = $this->isCustomerSubscribed($customer);

            foreach ($customer->getAddresses() as $customerAddress) {
                if ($customerAddress->isDefaultBilling()) {
                    $billingAddress = $this->addressFactory->createFromCustomer($customerAddress);
                }
                if ($customerAddress->isDefaultShipping()) {
                    $shippingAddress = $this->addressFactory->createFromCustomer($customerAddress);
                }
            }
        }

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
            (string) $order->getCustomerFirstname(),
            (string) $order->getCustomerLastname(),
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

    public function createFromCustomerAddress(AddressInterface $address): Customer
    {
        $customerId = (int)$address->getCustomerId();
        $customer = $this->customerRepository->getById($customerId);
        $isSubscribed = $this->isCustomerSubscribed($customer);

        $billingAddress = null;
        $shippingAddress = null;

        foreach ($customer->getAddresses() as $customerAddress) {
            if ($customerAddress->isDefaultBilling()) {
                $billingAddress = $this->addressFactory->createFromCustomer($customerAddress);
            }
            if ($customerAddress->isDefaultShipping()) {
                $shippingAddress = $this->addressFactory->createFromCustomer($customerAddress);
            }
        }

        if ($address->isDefaultBilling()) {
            $billingAddress = $this->addressFactory->createFromCustomer($address);
        }
        if ($address->isDefaultShipping()) {
            $shippingAddress = $this->addressFactory->createFromCustomer($address);
        }

        $customFields = [
            'website_id' => $customer->getWebsiteId(),
            'group_id' => $customer->getGroupId(),
            'store_id' => $customer->getStoreId(),
            'create_at' => $customer->getCreatedAt(),
            'prefix' => $customer->getPrefix(),
            'sufix' => $customer->getSuffix(),
            'dob' => $customer->getDob(),
            'tax_vat' => $customer->getTaxvat(),
            'gender' => $customer->getGender(),
            'middlename' => $customer->getMiddlename(),
        ];

        return new Customer(
            $customerId,
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

    public function createFromNewsletterSubscription(Subscriber $subscriber): Customer
    {
        $customerId = (int)$subscriber->getCustomerId();
        $customer = $this->customerRepository->getById($customerId);

        $billingAddress = null;
        $shippingAddress = null;
        foreach ($customer->getAddresses() as $address) {
            if ($address->isDefaultBilling()) {
                $billingAddress = $this->addressFactory->createFromCustomer($address);
            }
            if ($address->isDefaultShipping()) {
                $shippingAddress = $this->addressFactory->createFromCustomer($address);
            }
        }

        $customFields = [
            'website_id' => $customer->getWebsiteId(),
            'group_id' => $customer->getGroupId(),
            'store_id' => $customer->getStoreId(),
            'create_at' => $customer->getCreatedAt(),
            'prefix' => $customer->getPrefix(),
            'sufix' => $customer->getSuffix(),
            'dob' => $customer->getDob(),
            'tax_vat' => $customer->getTaxvat(),
            'gender' => $customer->getGender(),
            'middlename' => $customer->getMiddlename(),
        ];

        return new Customer(
            $customerId,
            $customer->getEmail(),
            $customer->getFirstname(),
            $customer->getLastname(),
            $subscriber->isSubscribed(),
            $billingAddress,
            [],
            array_merge(
                $customFields,
                null !== $billingAddress ? $billingAddress->toCustomFieldsArray('billing') : [],
                null !== $shippingAddress ? $shippingAddress->toCustomFieldsArray('shipping') : []
            )
        );
    }

    private function isCustomerSubscribed(CustomerInterface $customer): bool
    {
        /** @var Subscriber $subscriber */
        $subscriber = $this->subscriberCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addStoreFilter([$customer->getStoreId()])
            ->getFirstItem();

        return $subscriber && $subscriber->isSubscribed();
    }
}
