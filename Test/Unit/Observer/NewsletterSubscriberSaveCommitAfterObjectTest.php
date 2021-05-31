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
use GetResponse\GetResponseIntegration\Observer\NewsletterSubscriberSaveCommitAfterObject;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\Event\Observer;
use Magento\Newsletter\Model\Subscriber;
use PHPUnit\Framework\MockObject\MockObject;

class NewsletterSubscriberSaveCommitAfterObjectTest extends BaseTestCase
{
    /** @var Repository|MockObject */
    private $repositoryMock;
    /** @var ApiService|MockObject */
    private $apiServiceMock;
    /** @var NewsletterSubscriberSaveCommitAfterObject */
    private $sut;

    public function setUp(): void
    {
        /** @var ContactService|MockObject $contactServiceMock */
        $contactServiceMock = $this->getMockWithoutConstructing(ContactService::class);
        /** @var SubscribeViaRegistrationService|MockObject $subscribeViaRegistrationServiceMock */
        $subscribeViaRegistrationServiceMock = $this->getMockWithoutConstructing(SubscribeViaRegistrationService::class);
        /** @var ContactCustomFieldsCollectionFactory|MockObject $contactCustomFieldsCollectionFactoryMock */
        $contactCustomFieldsCollectionFactoryMock = $this->getMockWithoutConstructing(ContactCustomFieldsCollectionFactory::class);
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockWithoutConstructing(Logger::class);
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);
        $this->apiServiceMock = $this->getMockWithoutConstructing(ApiService::class);

        $this->sut = new NewsletterSubscriberSaveCommitAfterObject(
            $contactServiceMock,
            $subscribeViaRegistrationServiceMock,
            $contactCustomFieldsCollectionFactoryMock,
            $loggerMock,
            $this->repositoryMock,
            $this->apiServiceMock
        );
    }

    /**
     * @test
     */
    public function shouldUpsertCustomerSubscription(): void
    {
        $storeId = 3;
        $customerId = 200043;

        /** @var Subscriber|MockObject $subscriberMock */
        $subscriberMock = $this->getMockBuilder(Subscriber::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'getStoreId'])
            ->getMock();
        $subscriberMock->method('getCustomerId')->willReturn($customerId);
        $subscriberMock->method('getStoreId')->willReturn($storeId);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSubscriber'])
            ->getMock();
        $observerMock->method('getSubscriber')->willReturn($subscriberMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_NEW);

        $this->apiServiceMock
            ->expects(self::once())
            ->method('upsertCustomerSubscription')
            ->with($subscriberMock, new Scope($storeId));

        $this->sut->execute($observerMock);
    }

    /**
     * @test
     */
    public function shouldNotUpsertCustomerSubscriptionWhenOldPluginMode(): void
    {
        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSubscriber'])
            ->getMock();

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_OLD);

        $this->apiServiceMock
            ->expects(self::never())
            ->method('upsertCustomerSubscription');

        $this->sut->execute($observerMock);
    }

    /**
     * @test
     */
    public function shouldNotUpsertCustomerSubscriptionWhenEmptyCustomerId(): void
    {
        $storeId = 3;

        /** @var Subscriber|MockObject $subscriberMock */
        $subscriberMock = $this->getMockBuilder(Subscriber::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'getStoreId'])
            ->getMock();
        $subscriberMock->method('getCustomerId')->willReturn(null);
        $subscriberMock->method('getStoreId')->willReturn($storeId);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSubscriber'])
            ->getMock();
        $observerMock->method('getSubscriber')->willReturn($subscriberMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_NEW);

        $this->apiServiceMock
            ->expects(self::never())
            ->method('upsertCustomerSubscription');

        $this->sut->execute($observerMock);
    }
}
