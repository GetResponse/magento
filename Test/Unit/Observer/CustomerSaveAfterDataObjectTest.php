<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationService;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
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
    /** @var Repository|MockObject */
    private $repositoryMock;
    /** @var ApiService|MockObject */
    private $apiServiceMock;
    /** @var CustomerSaveAfterDataObject */
    private $sut;

    public function setUp(): void
    {
        parent::setUp();

        /** @var ContactService|MockObject $contactServiceMock */
        $contactServiceMock = $this->getMockWithoutConstructing(ContactService::class);
        /** @var SubscribeViaRegistrationService|MockObject $subscribeViaRegistrationServiceMock */
        $subscribeViaRegistrationServiceMock = $this->getMockWithoutConstructing(SubscribeViaRegistrationService::class);
        /** @var ContactCustomFieldsCollectionFactory|MockObject $contactCustomFieldsCollectionFactoryMock */
        $contactCustomFieldsCollectionFactoryMock = $this->getMockWithoutConstructing(ContactCustomFieldsCollectionFactory::class);
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);
        $this->apiServiceMock = $this->getMockWithoutConstructing(ApiService::class);
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockWithoutConstructing(Logger::class);

        $this->sut = new CustomerSaveAfterDataObject(
            $contactServiceMock,
            $subscribeViaRegistrationServiceMock,
            $contactCustomFieldsCollectionFactoryMock,
            $this->repositoryMock,
            $this->apiServiceMock,
            $loggerMock
        );
    }

    /**
     * @test
     */
    public function shouldUpsertCustomer(): void
    {
        $storeId = 3;
        $customerId = 23001;

        /** @var Customer|MockObject $customerMock */
        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', 'getId'])
            ->getMock();
        $customerMock->method('getStoreId')->willReturn($storeId);
        $customerMock->method('getId')->willReturn($customerId);
        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerDataObject'])
            ->getMock();
        $observerMock->method('getCustomerDataObject')->willReturn($customerMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_NEW);

        $this->apiServiceMock
            ->expects(self::once())
            ->method('upsertCustomer')
            ->with($customerMock, new Scope($storeId));

        $this->sut->execute($observerMock);
    }
}
