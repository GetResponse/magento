<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Application\GetResponse\TrackingCode\OrderService as TrackingCodeOrderService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Observer\OrderObserver;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Model\Order as MagentoOrder;
use PHPUnit\Framework\MockObject\MockObject;

class OrderObserverTest extends BaseTestCase
{
    /** @var ApiService|MockObject */
    private $apiServiceMock;
    /** @var TrackingCodeOrderService|MockObject */
    private $trackingCodeOrderServiceMock;
    /** @var OrderObserver */
    private $sut;

    protected function setUp(): void
    {
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockWithoutConstructing(Logger::class);
        $this->apiServiceMock = $this->getMockWithoutConstructing(ApiService::class);
        $this->trackingCodeOrderServiceMock = $this->getMockWithoutConstructing(TrackingCodeOrderService::class);

        $this->sut = new OrderObserver(
            $loggerMock,
            $this->apiServiceMock,
            $this->trackingCodeOrderServiceMock
        );
    }

    /**
     * @test
     */
    public function shouldCreateOrder(): void
    {
        $storeId = 3;
        $scope = new Scope($storeId);

        $orderMock = $this->getMockWithoutConstructing(MagentoOrder::class);
        $orderMock->method('getStoreId')->willReturn($storeId);

        $observerMock = $this->getMockWithoutConstructing(EventObserver::class, [], ['getOrder']);
        $observerMock->method('getOrder')->willReturn($orderMock);

        $this->apiServiceMock
            ->expects(self::once())
            ->method('createOrder')
            ->with($orderMock, $scope);

        $this->trackingCodeOrderServiceMock
            ->expects(self::once())
            ->method('addToBuffer')
            ->with($orderMock, $scope);

        $this->sut->execute($observerMock);
    }
}
