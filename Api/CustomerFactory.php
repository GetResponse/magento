<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Quote\Model\Quote as MagentoQuote;
use Magento\Sales\Model\Order as MagentoOrder;
use RuntimeException;

class CustomerFactory
{
    private $customerRepository;
    private $addressFactory;
    private $subscriberCollectionFactory;


    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        AddressFactory $addressFactory,
        CollectionFactory $subscriberCollectionFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->addressFactory = $addressFactory;
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
    }

    public function create(CustomerInterface $customer): Customer
    {
        $billingAddress = $shippingAddress = null;

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
            (int) $customer->getId(),
            $customer->getEmail(),
            $customer->getFirstname(),
            $customer->getLastname(),
            $this->isCustomerSubscribed($customer),
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
        return $order->getCustomerIsGuest() ? $this->createForGuestOrder($order) : $this->createForLoggedInOrder($order);
    }

    public function createFromQuote(MagentoQuote $quote): Customer
    {
        $billingAddress = $shippingAddress = null;

        $customer = $quote->getCustomer();

        foreach ($customer->getAddresses() as $customerAddress) {
            if ($customerAddress->isDefaultBilling()) {
                $billingAddress = $this->addressFactory->createFromCustomer($customerAddress);
            }
            if ($customerAddress->isDefaultShipping()) {
                $shippingAddress = $this->addressFactory->createFromCustomer($customerAddress);
            }
        }

        $customFields = [
            'group_id' => $customer->getCustomerGroupId(),
            'store_id' => $customer->getStoreId(),
            'prefix' => $customer->getCustomerPrefix(),
            'dob' => $customer->getCustomerDob(),
            'tax_vat' => $customer->getCustomerTaxvat(),
            'gender' => $customer->getCustomerGender(),
            'middlename' => $customer->getCustomerMiddlename(),
        ];

        return new Customer(
            (int) $customer->getId(),
            $customer->getEmail(),
            (string) $customer->getFirstname(),
            (string) $customer->getLastname(),
            $this->isCustomerSubscribed($customer),
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
        if (null === $address->getCustomerId()) {
            throw new RuntimeException('Cannot find customer id from address');
        }

        $customer = $this->customerRepository->getById($address->getCustomerId());

        $billingAddress = $shippingAddress = null;

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
            (int) $customer->getId(),
            $customer->getEmail(),
            $customer->getFirstname(),
            $customer->getLastname(),
            $this->isCustomerSubscribed($customer),
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
        if (null === $subscriber->getCustomerId()) {
            throw new RuntimeException('Cannot find customer id from subscriber');
        }

        $customer = $this->customerRepository->getById($subscriber->getCustomerId());

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
            (int) $customer->getId(),
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

    private function createForGuestOrder(MagentoOrder $order): Customer
    {
        $billingAddress = $shippingAddress = null;

        if ($order->getBillingAddress() !== null) {
            $billingAddress = $this->addressFactory->createFromOrder($order->getBillingAddress());
        }

        if ($order->getShippingAddress() !== null) {
            $shippingAddress = $this->addressFactory->createFromOrder($order->getShippingAddress());
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
            0,
            $order->getCustomerEmail(),
            (string) $order->getCustomerFirstname(),
            (string) $order->getCustomerLastname(),
            false,
            $billingAddress,
            [],
            array_merge(
                $customFields,
                null !== $billingAddress ? $billingAddress->toCustomFieldsArray('billing') : [],
                null !== $shippingAddress ? $shippingAddress->toCustomFieldsArray('shipping') : []
            )
        );
    }

    private function createForLoggedInOrder(MagentoOrder $order): Customer
    {
        $billingAddress = $shippingAddress = null;

        $customer = $this->customerRepository->getById($order->getCustomerId());

        foreach ($customer->getAddresses() as $customerAddress) {
            if ($customerAddress->isDefaultBilling()) {
                $billingAddress = $this->addressFactory->createFromCustomer($customerAddress);
            }
            if ($customerAddress->isDefaultShipping()) {
                $shippingAddress = $this->addressFactory->createFromCustomer($customerAddress);
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
            (int) $customer->getId(),
            $order->getCustomerEmail(),
            (string) $order->getCustomerFirstname(),
            (string) $order->getCustomerLastname(),
            $this->isCustomerSubscribed($customer),
            $billingAddress,
            [],
            array_merge(
                $customFields,
                null !== $billingAddress ? $billingAddress->toCustomFieldsArray('billing') : [],
                null !== $shippingAddress ? $shippingAddress->toCustomFieldsArray('shipping') : []
            )
        );
    }
}
