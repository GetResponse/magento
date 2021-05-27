<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
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
    /** @var Repository|MockObject */
    private $repositoryMock;
    /** @var ApiService|MockObject */
    private $apiServiceMock;
    /** @var CustomerAddressSaveAfterObject */
    private $sut;

    public function setUp(): void
    {
        parent::setUp();

        /** @var Logger|MockObject $logger */
        $logger = $this->getMockWithoutConstructing(Logger::class);
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);
        $this->apiServiceMock = $this->getMockWithoutConstructing(ApiService::class);

        $this->sut = new CustomerAddressSaveAfterObject($logger, $this->repositoryMock, $this->apiServiceMock);
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
        $addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDataModel', 'getStoreId'])
            ->getMock();
        $addressMock->method('getDataModel')->willReturn($addressModelMock);
        $addressMock->method('getStoreId')->willReturn($storeId);
        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerAddress'])
            ->getMock();

        $observerMock->method('getCustomerAddress')->willReturn($addressMock);

        $this->repositoryMock->expects(self::once())->method('getPluginMode')->willReturn(PluginMode::MODE_NEW);

        $this->apiServiceMock
            ->expects(self::once())
            ->method('upsertCustomerAddress')
            ->with($addressModelMock, new Scope($storeId));

        $this->sut->execute($observerMock);
    }

    /**
     * @test
     */
    public function shouldNotUpdateCustomerAddressWhenOldPluginVersion(): void
    {
        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerAddress'])
            ->getMock();

        $this->repositoryMock->expects(self::once())->method('getPluginMode')->willReturn(PluginMode::MODE_OLD);

        $this->apiServiceMock
            ->expects(self::never())
            ->method('upsertCustomerAddress');

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
        $addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDataModel', 'getStoreId', 'getData'])
            ->getMock();
        $addressMock->method('getDataModel')->willReturn($addressModelMock);
        $addressMock->method('getStoreId')->willReturn($storeId);
        $addressMock->method('getData')->willReturn(false);
        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerAddress'])
            ->getMock();

        $observerMock->method('getCustomerAddress')->willReturn($addressMock);

        $this->repositoryMock->expects(self::once())->method('getPluginMode')->willReturn(PluginMode::MODE_NEW);

        $this->apiServiceMock
            ->expects(self::never())
            ->method('upsertCustomerAddress');

        $this->sut->execute($observerMock);
    }
}
