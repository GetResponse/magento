<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\AddressFactory;
use GetResponse\GetResponseIntegration\Api\Customer;
use GetResponse\GetResponseIntegration\Api\CustomerFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GetResponse\GetResponseIntegration\Test\Unit\ApiFaker;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Newsletter\Model\Subscriber;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Sales\Model\Order as MagentoOrder;

class CustomerFactoryTest extends BaseTestCase
{
    /** @var CustomerRepositoryInterface|MockObject */
    private $customerRepositoryMock;
    /** @var Subscriber|MockObject */
    private $subscriberMock;
    /** @var AddressFactory|MockObject */
    private $addressFactoryMock;
    /** @var CustomerFactory */
    private $sut;

    protected function setUp(): void
    {
        $this->customerRepositoryMock = $this->getMockWithoutConstructing(CustomerRepositoryInterface::class);
        $this->subscriberMock = $this->getMockWithoutConstructing(Subscriber::class);
        $this->addressFactoryMock = $this->getMockWithoutConstructing(AddressFactory::class);

        $this->sut = new CustomerFactory(
            $this->customerRepositoryMock,
            $this->subscriberMock,
            $this->addressFactoryMock
        );
    }

    /**
     * @test
     */
    public function shouldCreateFromCustomer(): void
    {
        $customerId = 100232;
        $customerEmail = 'some@email.com';
        $customerFirstName = 'John';
        $customerLastName = 'Smith';
        $websiteId = 2;
        $groupId = 32;
        $storeId = 4;
        $createAt = '2021-05-21 12:39:59';
        $prefix = 'my_';
        $sufix = '';
        $dob = '1985-09-21';
        $taxVat = '19';
        $gender = 1;
        $middleName = '';

        /** @var AddressInterface|MockObject $addressMock */
        $addressMock = $this->getMockWithoutConstructing(AddressInterface::class);
        $addressMock->method('isDefaultBilling')->willReturn(true);
        $addressMock->method('isDefaultShipping')->willReturn(true);

        /** @var CustomerInterface|MockObject $customerMock */
        $customerMock = $this->getMockWithoutConstructing(CustomerInterface::class);
        $customerMock->method('getId')->willReturn($customerId);
        $customerMock->method('getEmail')->willReturn($customerEmail);
        $customerMock->method('getFirstname')->willReturn($customerFirstName);
        $customerMock->method('getLastname')->willReturn($customerLastName);
        $customerMock->method('getAddresses')->willReturn([$addressMock]);
        $customerMock->method('getWebsiteId')->willReturn($websiteId);
        $customerMock->method('getGroupId')->willReturn($groupId);
        $customerMock->method('getStoreId')->willReturn($storeId);
        $customerMock->method('getCreatedAt')->willReturn($createAt);
        $customerMock->method('getPrefix')->willReturn($prefix);
        $customerMock->method('getSuffix')->willReturn($sufix);
        $customerMock->method('getDob')->willReturn($dob);
        $customerMock->method('getTaxvat')->willReturn($taxVat);
        $customerMock->method('getGender')->willReturn($gender);
        $customerMock->method('getMiddlename')->willReturn($middleName);

        $this->subscriberMock->method('isSubscribed')->willReturn(true);
        $this->subscriberMock->method('loadByCustomerId')->willReturn($this->subscriberMock);

        $address = ApiFaker::createAddress();

        $this->addressFactoryMock->method('createFromCustomer')->willReturn($address);

        $expectedCustomer = new Customer(
            $customerId,
            $customerEmail,
            $customerFirstName,
            $customerLastName,
            true,
            $address,
            [],
            [
                'website_id' => $websiteId,
                'group_id' => $groupId,
                'store_id' => $storeId,
                'create_at' => $createAt,
                'prefix' => $prefix,
                'sufix' => $sufix,
                'dob' => $dob,
                'tax_vat' => $taxVat,
                'gender' => $gender,
                'middlename' => $middleName,
                'billing_name' => 'Brian Sings',
                'billing_country_code' => 'OK',
                'billing_first_name' => 'Brian',
                'billing_last_name' => 'Sings',
                'billing_address1' => '4508  Memory Lane',
                'billing_address2' => null,
                'billing_city' => 'GUTHRIE',
                'billing_zip_code' => '73044',
                'billing_province' => 'Oklahoma',
                'billing_province_code' => null,
                'billing_phone' => '544404400',
                'billing_company' => null,
                'shipping_name' => 'Brian Sings',
                'shipping_country_code' => 'OK',
                'shipping_first_name' => 'Brian',
                'shipping_last_name' => 'Sings',
                'shipping_address1' => '4508  Memory Lane',
                'shipping_address2' => null,
                'shipping_city' => 'GUTHRIE',
                'shipping_zip_code' => '73044',
                'shipping_province' => 'Oklahoma',
                'shipping_province_code' => null,
                'shipping_phone' => '544404400',
                'shipping_company' => null,
            ]
        );

        $customer = $this->sut->create($customerMock);
        self::assertEquals($expectedCustomer, $customer);
    }

    /**
     * @test
     */
    public function shouldCreateFromOrder(): void
    {
        $customerId = 100232;
        $customerEmail = 'some@email.com';
        $customerFirstName = 'John';
        $customerLastName = 'Smith';
        $groupId = 32;
        $storeId = 4;
        $prefix = 'my_';
        $dob = '1985-09-21';
        $taxVat = '19';
        $gender = 1;
        $middleName = '';

        /** @var MagentoOrder|MockObject $orderMock */
        $orderMock = $this->getMockWithoutConstructing(MagentoOrder::class);
        $orderMock->method('getCustomerId')->willReturn($customerId);

        $orderMock->method('getCustomerEmail')->willReturn($customerEmail);
        $orderMock->method('getCustomerFirstname')->willReturn($customerFirstName);
        $orderMock->method('getCustomerLastname')->willReturn($customerLastName);

        $orderMock->method('getCustomerGroupId')->willReturn($groupId);
        $orderMock->method('getStoreId')->willReturn($storeId);
        $orderMock->method('getCustomerPrefix')->willReturn($prefix);
        $orderMock->method('getCustomerDob')->willReturn($dob);
        $orderMock->method('getCustomerTaxvat')->willReturn($taxVat);
        $orderMock->method('getCustomerGender')->willReturn($gender);
        $orderMock->method('getCustomerMiddlename')->willReturn($middleName);

        $this->subscriberMock->method('isSubscribed')->willReturn(true);
        $this->subscriberMock->method('loadByCustomerId')->willReturn($this->subscriberMock);

        /** @var AddressInterface|MockObject $addressMock */
        $addressMock = $this->getMockWithoutConstructing(AddressInterface::class);
        $addressMock->method('isDefaultBilling')->willReturn(true);
        $addressMock->method('isDefaultShipping')->willReturn(true);

        /** @var CustomerInterface|MockObject $customerMock */
        $customerMock = $this->getMockWithoutConstructing(CustomerInterface::class);
        $customerMock->method('getAddresses')->willReturn([$addressMock]);

        $this->customerRepositoryMock->method('getById')->willReturn($customerMock);

        $address = ApiFaker::createAddress();

        $this->addressFactoryMock->method('createFromCustomer')->willReturn($address);

        $expectedCustomer = new Customer(
            $customerId,
            $customerEmail,
            $customerFirstName,
            $customerLastName,
            true,
            $address,
            [],
            [
                'group_id' => $groupId,
                'store_id' => $storeId,
                'prefix' => $prefix,
                'dob' => $dob,
                'tax_vat' => $taxVat,
                'gender' => $gender,
                'middlename' => $middleName,
                'billing_name' => 'Brian Sings',
                'billing_country_code' => 'OK',
                'billing_first_name' => 'Brian',
                'billing_last_name' => 'Sings',
                'billing_address1' => '4508  Memory Lane',
                'billing_address2' => null,
                'billing_city' => 'GUTHRIE',
                'billing_zip_code' => '73044',
                'billing_province' => 'Oklahoma',
                'billing_province_code' => null,
                'billing_phone' => '544404400',
                'billing_company' => null,
                'shipping_name' => 'Brian Sings',
                'shipping_country_code' => 'OK',
                'shipping_first_name' => 'Brian',
                'shipping_last_name' => 'Sings',
                'shipping_address1' => '4508  Memory Lane',
                'shipping_address2' => null,
                'shipping_city' => 'GUTHRIE',
                'shipping_zip_code' => '73044',
                'shipping_province' => 'Oklahoma',
                'shipping_province_code' => null,
                'shipping_phone' => '544404400',
                'shipping_company' => null,
            ]
        );

        $customer = $this->sut->createFromOrder($orderMock);
        self::assertEquals($expectedCustomer, $customer);
    }

    /**
     * @test
     */
    public function shouldCreateFromCustomerAddress(): void
    {
        $customerId = 100232;
        $customerEmail = 'some@email.com';
        $customerFirstName = 'John';
        $customerLastName = 'Smith';
        $websiteId = 2;
        $groupId = 32;
        $storeId = 4;
        $createAt = '2021-05-21 12:39:59';
        $prefix = 'my_';
        $sufix = '';
        $dob = '1985-09-21';
        $taxVat = '19';
        $gender = 1;
        $middleName = '';

        /** @var AddressInterface|MockObject $addressMock */
        $addressMock = $this->getMockWithoutConstructing(AddressInterface::class);
        $addressMock->method('getCustomerId')->willReturn($customerId);

        $this->subscriberMock->method('isSubscribed')->willReturn(true);
        $this->subscriberMock->method('loadByCustomerId')->willReturn($this->subscriberMock);

        $addressMock->method('isDefaultBilling')->willReturn(true);
        $addressMock->method('isDefaultShipping')->willReturn(true);

        /** @var CustomerInterface|MockObject $customerMock */
        $customerMock = $this->getMockWithoutConstructing(CustomerInterface::class);
        $customerMock->method('getAddresses')->willReturn([$addressMock]);
        $customerMock->method('getEmail')->willReturn($customerEmail);
        $customerMock->method('getFirstname')->willReturn($customerFirstName);
        $customerMock->method('getLastname')->willReturn($customerLastName);
        $customerMock->method('getWebsiteId')->willReturn($websiteId);
        $customerMock->method('getGroupId')->willReturn($groupId);
        $customerMock->method('getStoreId')->willReturn($storeId);
        $customerMock->method('getCreatedAt')->willReturn($createAt);
        $customerMock->method('getPrefix')->willReturn($prefix);
        $customerMock->method('getSuffix')->willReturn($sufix);
        $customerMock->method('getDob')->willReturn($dob);
        $customerMock->method('getTaxvat')->willReturn($taxVat);
        $customerMock->method('getGender')->willReturn($gender);
        $customerMock->method('getMiddlename')->willReturn($middleName);

        $this->customerRepositoryMock->method('getById')->willReturn($customerMock);

        $address = ApiFaker::createAddress();

        $this->addressFactoryMock->method('createFromCustomer')->willReturn($address);

        $expectedCustomer = new Customer(
            $customerId,
            $customerEmail,
            $customerFirstName,
            $customerLastName,
            true,
            $address,
            [],
            [
                'website_id' => $websiteId,
                'group_id' => $groupId,
                'store_id' => $storeId,
                'create_at' => $createAt,
                'prefix' => $prefix,
                'sufix' => $sufix,
                'dob' => $dob,
                'tax_vat' => $taxVat,
                'gender' => $gender,
                'middlename' => $middleName,
                'billing_name' => 'Brian Sings',
                'billing_country_code' => 'OK',
                'billing_first_name' => 'Brian',
                'billing_last_name' => 'Sings',
                'billing_address1' => '4508  Memory Lane',
                'billing_address2' => null,
                'billing_city' => 'GUTHRIE',
                'billing_zip_code' => '73044',
                'billing_province' => 'Oklahoma',
                'billing_province_code' => null,
                'billing_phone' => '544404400',
                'billing_company' => null,
                'shipping_name' => 'Brian Sings',
                'shipping_country_code' => 'OK',
                'shipping_first_name' => 'Brian',
                'shipping_last_name' => 'Sings',
                'shipping_address1' => '4508  Memory Lane',
                'shipping_address2' => null,
                'shipping_city' => 'GUTHRIE',
                'shipping_zip_code' => '73044',
                'shipping_province' => 'Oklahoma',
                'shipping_province_code' => null,
                'shipping_phone' => '544404400',
                'shipping_company' => null,
            ]
        );

        $customer = $this->sut->createFromCustomerAddress($addressMock);
        self::assertEquals($expectedCustomer, $customer);
    }

    /**
     * @test
     */
    public function shouldCreateFromNewsletterSubscription(): void
    {
        $customerId = 100232;
        $customerEmail = 'some@email.com';
        $customerFirstName = 'John';
        $customerLastName = 'Smith';
        $websiteId = 2;
        $groupId = 32;
        $storeId = 4;
        $createAt = '2021-05-21 12:39:59';
        $prefix = 'my_';
        $sufix = '';
        $dob = '1985-09-21';
        $taxVat = '19';
        $gender = 1;
        $middleName = '';

        /** @var Subscriber|MockObject $subscriberMock */
        $subscriberMock = $this->getMockWithoutConstructing(
            Subscriber::class,
            ['isSubscribed'],
            ['getCustomerId']
        );

        $subscriberMock->method('isSubscribed')->willReturn(true);
        $subscriberMock->method('getCustomerId')->willReturn($customerId);

        /** @var AddressInterface|MockObject $orderMock */
        $addressMock = $this->getMockWithoutConstructing(AddressInterface::class);
        $addressMock->method('getCustomerId')->willReturn($customerId);

        $addressMock->method('isDefaultBilling')->willReturn(true);
        $addressMock->method('isDefaultShipping')->willReturn(true);

        /** @var CustomerInterface|MockObject $customerMock */
        $customerMock = $this->getMockWithoutConstructing(CustomerInterface::class);
        $customerMock->method('getAddresses')->willReturn([$addressMock]);
        $customerMock->method('getEmail')->willReturn($customerEmail);
        $customerMock->method('getFirstname')->willReturn($customerFirstName);
        $customerMock->method('getLastname')->willReturn($customerLastName);
        $customerMock->method('getWebsiteId')->willReturn($websiteId);
        $customerMock->method('getGroupId')->willReturn($groupId);
        $customerMock->method('getStoreId')->willReturn($storeId);
        $customerMock->method('getCreatedAt')->willReturn($createAt);
        $customerMock->method('getPrefix')->willReturn($prefix);
        $customerMock->method('getSuffix')->willReturn($sufix);
        $customerMock->method('getDob')->willReturn($dob);
        $customerMock->method('getTaxvat')->willReturn($taxVat);
        $customerMock->method('getGender')->willReturn($gender);
        $customerMock->method('getMiddlename')->willReturn($middleName);

        $this->customerRepositoryMock->method('getById')->willReturn($customerMock);

        $address = ApiFaker::createAddress();

        $this->addressFactoryMock->method('createFromCustomer')->willReturn($address);

        $expectedCustomer = new Customer(
            $customerId,
            $customerEmail,
            $customerFirstName,
            $customerLastName,
            true,
            $address,
            [],
            [
                'website_id' => $websiteId,
                'group_id' => $groupId,
                'store_id' => $storeId,
                'create_at' => $createAt,
                'prefix' => $prefix,
                'sufix' => $sufix,
                'dob' => $dob,
                'tax_vat' => $taxVat,
                'gender' => $gender,
                'middlename' => $middleName,
                'billing_name' => 'Brian Sings',
                'billing_country_code' => 'OK',
                'billing_first_name' => 'Brian',
                'billing_last_name' => 'Sings',
                'billing_address1' => '4508  Memory Lane',
                'billing_address2' => null,
                'billing_city' => 'GUTHRIE',
                'billing_zip_code' => '73044',
                'billing_province' => 'Oklahoma',
                'billing_province_code' => null,
                'billing_phone' => '544404400',
                'billing_company' => null,
                'shipping_name' => 'Brian Sings',
                'shipping_country_code' => 'OK',
                'shipping_first_name' => 'Brian',
                'shipping_last_name' => 'Sings',
                'shipping_address1' => '4508  Memory Lane',
                'shipping_address2' => null,
                'shipping_city' => 'GUTHRIE',
                'shipping_zip_code' => '73044',
                'shipping_province' => 'Oklahoma',
                'shipping_province_code' => null,
                'shipping_phone' => '544404400',
                'shipping_company' => null,
            ]
        );

        $customer = $this->sut->createFromNewsletterSubscription($subscriberMock);
        self::assertEquals($expectedCustomer, $customer);
    }

    public function shouldCreateFromCustomerWithEmptyName(): void
    {
        $customerId = 100232;
        $customerEmail = 'some@email.com';
        $websiteId = 2;
        $groupId = 32;
        $storeId = 4;
        $createAt = '2021-05-21 12:39:59';
        $prefix = 'my_';
        $sufix = '';
        $dob = '1985-09-21';
        $taxVat = '19';
        $gender = 1;
        $middleName = '';

        /** @var AddressInterface|MockObject $addressMock */
        $addressMock = $this->getMockWithoutConstructing(AddressInterface::class);
        $addressMock->method('isDefaultBilling')->willReturn(true);
        $addressMock->method('isDefaultShipping')->willReturn(true);

        /** @var CustomerInterface|MockObject $customerMock */
        $customerMock = $this->getMockWithoutConstructing(CustomerInterface::class);
        $customerMock->method('getId')->willReturn($customerId);
        $customerMock->method('getEmail')->willReturn($customerEmail);
        $customerMock->method('getFirstname')->willReturn(null);
        $customerMock->method('getLastname')->willReturn(null);
        $customerMock->method('getAddresses')->willReturn([$addressMock]);
        $customerMock->method('getWebsiteId')->willReturn($websiteId);
        $customerMock->method('getGroupId')->willReturn($groupId);
        $customerMock->method('getStoreId')->willReturn($storeId);
        $customerMock->method('getCreatedAt')->willReturn($createAt);
        $customerMock->method('getPrefix')->willReturn($prefix);
        $customerMock->method('getSuffix')->willReturn($sufix);
        $customerMock->method('getDob')->willReturn($dob);
        $customerMock->method('getTaxvat')->willReturn($taxVat);
        $customerMock->method('getGender')->willReturn($gender);
        $customerMock->method('getMiddlename')->willReturn($middleName);

        $this->subscriberMock->method('isSubscribed')->willReturn(true);
        $this->subscriberMock->method('loadByCustomerId')->willReturn($this->subscriberMock);

        $address = ApiFaker::createAddress();

        $this->addressFactoryMock->method('createFromCustomer')->willReturn($address);

        $expectedCustomer = new Customer(
            $customerId,
            $customerEmail,
            '',
            '',
            true,
            $address,
            [],
            [
                'website_id' => $websiteId,
                'group_id' => $groupId,
                'store_id' => $storeId,
                'create_at' => $createAt,
                'prefix' => $prefix,
                'sufix' => $sufix,
                'dob' => $dob,
                'tax_vat' => $taxVat,
                'gender' => $gender,
                'middlename' => $middleName,
                'billing_name' => 'Brian Sings',
                'billing_country_code' => 'OK',
                'billing_first_name' => 'Brian',
                'billing_last_name' => 'Sings',
                'billing_address1' => '4508  Memory Lane',
                'billing_address2' => null,
                'billing_city' => 'GUTHRIE',
                'billing_zip_code' => '73044',
                'billing_province' => 'Oklahoma',
                'billing_province_code' => null,
                'billing_phone' => '544404400',
                'billing_company' => null,
                'shipping_name' => 'Brian Sings',
                'shipping_country_code' => 'OK',
                'shipping_first_name' => 'Brian',
                'shipping_last_name' => 'Sings',
                'shipping_address1' => '4508  Memory Lane',
                'shipping_address2' => null,
                'shipping_city' => 'GUTHRIE',
                'shipping_zip_code' => '73044',
                'shipping_province' => 'Oklahoma',
                'shipping_province_code' => null,
                'shipping_phone' => '544404400',
                'shipping_company' => null,
            ]
        );

        $customer = $this->sut->create($customerMock);
        self::assertEquals($expectedCustomer, $customer);
    }
}
