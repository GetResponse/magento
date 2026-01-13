<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Observer\CustomerAddressSaveAfterObject;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;

class CustomerAddressSaveAfterObjectTest extends BaseTestCase
{
    /** @var ApiService|MockObject */
    private $apiServiceMock;
    /** @var CustomerAddressSaveAfterObject */
    private $sut;

    protected function setUp(): void
    {
        /** @var Logger|MockObject $logger */
        $logger = $this->getMockWithoutConstructing(Logger::class);
        $this->apiServiceMock = $this->getMockWithoutConstructing(ApiService::class);

        $this->sut = new CustomerAddressSaveAfterObject($logger, $this->apiServiceMock);
    }

    /**
     * @test
     */
    public function shouldUpdateCustomerAddress(): void
    {
        $storeId = 3;

        /** @var AddressInterface|MockObject $addressModelMock */
        $addressModelMock = $this->getMockWithoutConstructing(AddressInterface::class);
        $addressModelMock->method('isDefaultBilling')->willReturn(true);
        $addressModelMock->method('isDefaultShipping')->willReturn(false);
        /** @var Address|MockObject $addressMock */
        $addressMock = $this->getMockWithoutConstructing(Address::class, ['getDataModel'], ['getStoreId']);
        $addressMock->method('getDataModel')->willReturn($addressModelMock);
        $addressMock->method('getStoreId')->willReturn($storeId);
        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(Observer::class, [], ['getCustomerAddress']);

        $observerMock->method('getCustomerAddress')->willReturn($addressMock);

        $this->apiServiceMock
            ->expects(self::once())
            ->method('upsertCustomerAddress')
            ->with($addressModelMock, new Scope($storeId));

        $this->sut->execute($observerMock);
    }

    /**
     * @test
     */
    public function shouldNotUpdateCustomerAddressWhenEmptyAddress(): void
    {
        $storeId = 3;

        /** @var AddressInterface|MockObject $addressModelMock */
        $addressModelMock = $this->getMockWithoutConstructing(AddressInterface::class);
        $addressModelMock->method('isDefaultBilling')->willReturn(false);
        $addressModelMock->method('isDefaultShipping')->willReturn(false);
        /** @var Address|MockObject $addressMock */
        $addressMock = $this->getMockWithoutConstructing(Address::class, ['getDataModel', 'getData'], ['getStoreId']);
        $addressMock->method('getDataModel')->willReturn($addressModelMock);
        $addressMock->method('getStoreId')->willReturn($storeId);
        $addressMock->method('getData')->willReturn(false);
        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(Observer::class, [], ['getCustomerAddress']);

        $observerMock->method('getCustomerAddress')->willReturn($addressMock);

        $this->apiServiceMock
            ->expects(self::never())
            ->method('upsertCustomerAddress');

        $this->sut->execute($observerMock);
    }
}
