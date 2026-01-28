<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Observer\NewsletterSubscriberSaveCommitAfterObject;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\Event\Observer;
use Magento\Newsletter\Model\Subscriber;
use PHPUnit\Framework\MockObject\MockObject;

class NewsletterSubscriberSaveCommitAfterObjectTest extends BaseTestCase
{
    /** @var ApiService|MockObject */
    private $apiServiceMock;
    /** @var NewsletterSubscriberSaveCommitAfterObject */
    private $sut;

    protected function setUp(): void
    {
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockWithoutConstructing(Logger::class);
        $this->apiServiceMock = $this->getMockWithoutConstructing(ApiService::class);

        $this->sut = new NewsletterSubscriberSaveCommitAfterObject($loggerMock, $this->apiServiceMock);
    }

    /**
     * @test
     */
    public function shouldUpsertCustomerSubscription(): void
    {
        $storeId = 3;
        $customerId = 200043;

        /** @var Subscriber|MockObject $subscriberMock */
        $subscriberMock = $this->getMockWithoutConstructing(Subscriber::class, [], ['getCustomerId', 'getStoreId']);
        $subscriberMock->method('getCustomerId')->willReturn($customerId);
        $subscriberMock->method('getStoreId')->willReturn($storeId);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(Observer::class, [], ['getSubscriber']);
        $observerMock->method('getSubscriber')->willReturn($subscriberMock);

        $this->apiServiceMock
            ->expects(self::once())
            ->method('upsertCustomerSubscription')
            ->with($subscriberMock, Scope::createFromStoreId($storeId));

        $this->sut->execute($observerMock);
    }

    /**
     * @test
     */
    public function shouldNotUpsertCustomerSubscriptionWhenOldPluginMode(): void
    {
        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(Observer::class, [], ['getSubscriber']);

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
        $subscriberMock = $this->getMockWithoutConstructing(Subscriber::class, [], ['getCustomerId', 'getStoreId']);
        $subscriberMock->method('getCustomerId')->willReturn(null);
        $subscriberMock->method('getStoreId')->willReturn($storeId);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(Observer::class, [], ['getSubscriber']);
        $observerMock->method('getSubscriber')->willReturn($subscriberMock);

        $this->apiServiceMock
            ->expects(self::never())
            ->method('upsertCustomerSubscription');

        $this->sut->execute($observerMock);
    }
}
