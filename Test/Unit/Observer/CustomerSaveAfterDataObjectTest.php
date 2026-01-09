<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Observer\CustomerSaveAfterDataObject;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;

class CustomerSaveAfterDataObjectTest extends BaseTestCase
{
    /** @var ApiService|MockObject */
    private $apiServiceMock;
    /** @var CustomerSaveAfterDataObject */
    private $sut;

    protected function setUp(): void
    {
        $this->apiServiceMock = $this->getMockWithoutConstructing(ApiService::class);
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockWithoutConstructing(Logger::class);

        $this->sut = new CustomerSaveAfterDataObject($this->apiServiceMock, $loggerMock);
    }

    /**
     * @test
     */
    public function shouldUpsertCustomer(): void
    {
        $storeId = 3;
        $customerId = 23001;

        /** @var Customer|MockObject $customerMock */
        $customerMock = $this->getMockWithoutConstructing(Customer::class, ['getStoreId', 'getId']);
        $customerMock->method('getStoreId')->willReturn($storeId);
        $customerMock->method('getId')->willReturn($customerId);
        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(Observer::class, [], ['getCustomerDataObject']);
        $observerMock->method('getCustomerDataObject')->willReturn($customerMock);

        $this->apiServiceMock
            ->expects(self::once())
            ->method('upsertCustomer')
            ->with($customerMock, new Scope($storeId));

        $this->sut->execute($observerMock);
    }
}
