<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\Address;
use GetResponse\GetResponseIntegration\Api\AddressFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use PHPUnit\Framework\MockObject\MockObject;

class AddressFactoryTest extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateFromCustomerAddress(): void
    {
        $name = 'John Smith';
        $countryCode = 'PL';
        $firstName = 'John';
        $lastName = 'Smith';
        $address1 = 'Street 12';
        $address2 = 'XYZ';
        $city = 'City';
        $zipCode = '99-001';
        $province = 'Province';
        $provinceCode = '9009';
        $phone = '544404400';
        $company = 'Company';

        $expectedAddress = new Address(
            $name,
            $countryCode,
            $firstName,
            $lastName,
            $address1,
            $address2,
            $city,
            $zipCode,
            $province,
            $provinceCode,
            $phone,
            $company
        );

        /** @var AddressInterface|MockObject $customerAddressMock */
        $customerAddressMock = $this->getMockWithoutConstructing(AddressInterface::class);
        /** @var RegionInterface|MockObject $customerRegionMock */
        $customerRegionMock = $this->getMockWithoutConstructing(RegionInterface::class);

        $customerAddressMock->method('getStreet')->willReturn([$address1, $address2]);
        $customerAddressMock->method('getFirstname')->willReturn($firstName);
        $customerAddressMock->method('getLastname')->willReturn($lastName);
        $customerAddressMock->method('getCountryId')->willReturn($countryCode);
        $customerAddressMock->method('getCity')->willReturn($city);
        $customerAddressMock->method('getPostcode')->willReturn($zipCode);
        $customerAddressMock->method('getRegion')->willReturn($customerRegionMock);
        $customerRegionMock->method('getRegion')->willReturn($province);
        $customerRegionMock->method('getRegionCode')->willReturn($provinceCode);
        $customerAddressMock->method('getTelephone')->willReturn($phone);
        $customerAddressMock->method('getCompany')->willReturn($company);

        $factory = new AddressFactory();
        $address = $factory->createFromCustomer($customerAddressMock);

        self::assertEquals($expectedAddress, $address);
    }

    /**
     * @test
     */
    public function shouldCreateFromOrderAddress(): void
    {
        $name = 'John Smith';
        $countryCode = 'PL';
        $firstName = 'John';
        $lastName = 'Smith';
        $address1 = 'Street 12';
        $address2 = 'XYZ';
        $city = 'City';
        $zipCode = '99-001';
        $province = 'Province';
        $provinceCode = '9009';
        $phone = '544404400';
        $company = 'Company';

        $expectedAddress = new Address(
            $name,
            $countryCode,
            $firstName,
            $lastName,
            $address1,
            $address2,
            $city,
            $zipCode,
            $province,
            $provinceCode,
            $phone,
            $company
        );

        /** @var OrderAddressInterface|MockObject $orderAddressMock */
        $orderAddressMock = $this->getMockWithoutConstructing(OrderAddressInterface::class);

        $orderAddressMock->method('getStreet')->willReturn([$address1, $address2]);
        $orderAddressMock->method('getFirstname')->willReturn($firstName);
        $orderAddressMock->method('getLastname')->willReturn($lastName);
        $orderAddressMock->method('getCountryId')->willReturn($countryCode);
        $orderAddressMock->method('getCity')->willReturn($city);
        $orderAddressMock->method('getPostcode')->willReturn($zipCode);
        $orderAddressMock->method('getRegion')->willReturn($province);
        $orderAddressMock->method('getRegionCode')->willReturn($provinceCode);
        $orderAddressMock->method('getTelephone')->willReturn($phone);
        $orderAddressMock->method('getCompany')->willReturn($company);

        $factory = new AddressFactory();
        $address = $factory->createFromOrder($orderAddressMock);

        self::assertEquals($expectedAddress, $address);
    }
}
