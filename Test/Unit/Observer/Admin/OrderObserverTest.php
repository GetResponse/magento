<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer\Admin;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Observer\Admin\OrderObserver;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\Event;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Event\Observer as EventObserver;

class OrderObserverTest extends BaseTestCase
{
    /** @var ApiService|MockObject */
    private $apiServiceMock;
    /** @var OrderObserver */
    private $sut;

    protected function setUp(): void
    {
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockWithoutConstructing(Logger::class);
        $this->apiServiceMock = $this->getMockWithoutConstructing(ApiService::class);

        $this->sut = new OrderObserver($loggerMock, $this->apiServiceMock);
    }

    /**
     * @test
     */
    public function shouldUpdateOrder(): void
    {
        $storeId = 3;

        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockWithoutConstructing(Order::class);
        $orderMock->method('getStoreId')->willReturn($storeId);
        /** @var Event|MockObject $eventMock */
        $eventMock = $this->getMockWithoutConstructing(Event::class, [], ['getOrder']);
        $eventMock->method('getOrder')->willReturn($orderMock);
        /** @var EventObserver|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(EventObserver::class);
        $observerMock->method('getEvent')->willReturn($eventMock);

        $this->apiServiceMock
            ->expects(self::once())
            ->method('updateOrder')
            ->with($orderMock, new Scope($storeId));

        $this->sut->execute($observerMock);
    }
}
