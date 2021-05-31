<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

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

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Subscriber $subscriber,
        AddressFactory $addressFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->subscriber = $subscriber;
        $this->addressFactory = $addressFactory;
    }

    public function create(CustomerInterface $customer): Customer
    {
        $customerId = $customer->getId();
        $isSubscribed = $this->isCustomerSubscribed((int)$customerId);

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
        $customerId = null === $order->getCustomerId() ? null : (int)$order->getCustomerId();
        $isSubscribed = $this->isCustomerSubscribed($customerId);

        $billingAddress = null;
        $shippingAddress = null;

        if (null !== $customerId) {
            $customer = $this->customerRepository->getById($customerId);
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
            $order->getCustomerFirstname(),
            $order->getCustomerLastname(),
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
        $isSubscribed = $this->isCustomerSubscribed($customerId);
        $customer = $this->customerRepository->getById($customerId);

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

    public function createFromNewsletterSubscriber(Subscriber $subscriber): Customer
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

    private function isCustomerSubscribed(?int $customerId): bool
    {
        if (null === $customerId) {
            return false;
        }

        $subscriber = $this->subscriber->loadByCustomerId($customerId);

        return $subscriber->isSubscribed();
    }
}
