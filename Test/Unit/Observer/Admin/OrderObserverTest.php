<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer\Admin;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\ContactReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Ecommerce\ReadModel\EcommerceReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Command\EditOrderCommandFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
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
    /** @var Repository|MockObject */
    private $repositoryMock;
    /** @var OrderObserver */
    private $sut;

    public function setUp(): void
    {
        /** @var OrderService|MockObject $orderServiceMock */
        $orderServiceMock = $this->getMockWithoutConstructing(OrderService::class);
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockWithoutConstructing(Logger::class);
        /** @var EditOrderCommandFactory|MockObject $editOrderCommandFactoryMock */
        $editOrderCommandFactoryMock = $this->getMockWithoutConstructing(EditOrderCommandFactory::class);
        /** @var EcommerceReadModel|MockObject $ecommerceReadModelMock */
        $ecommerceReadModelMock = $this->getMockWithoutConstructing(EcommerceReadModel::class);
        /** @var ContactReadModel|MockObject $contactReadModelMock */
        $contactReadModelMock = $this->getMockWithoutConstructing(ContactReadModel::class);
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);
        $this->apiServiceMock = $this->getMockWithoutConstructing(ApiService::class);

        $this->sut = new OrderObserver(
            $orderServiceMock,
            $loggerMock,
            $editOrderCommandFactoryMock,
            $ecommerceReadModelMock,
            $contactReadModelMock,
            $this->repositoryMock,
            $this->apiServiceMock
        );
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
        $eventMock = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->setMethods(['getOrder'])->getMock();
        $eventMock->method('getOrder')->willReturn($orderMock);
        /** @var EventObserver|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(EventObserver::class);
        $observerMock->method('getEvent')->willReturn($eventMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_NEW);

        $this->apiServiceMock
            ->expects(self::once())
            ->method('updateOrder')
            ->with($orderMock, new Scope($storeId));

        $this->sut->execute($observerMock);
    }

    /**
     * @test
     */
    public function shouldNotUpdateOrderWhenOldPluginVersion(): void
    {
        $storeId = 3;

        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockWithoutConstructing(Order::class);
        $orderMock->method('getStoreId')->willReturn($storeId);
        /** @var Event|MockObject $eventMock */
        $eventMock = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->setMethods(['getOrder'])->getMock();
        $eventMock->method('getOrder')->willReturn($orderMock);
        /** @var EventObserver|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(EventObserver::class);
        $observerMock->method('getEvent')->willReturn($eventMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_OLD);

        $this->apiServiceMock
            ->expects(self::never())
            ->method('updateOrder');

        $this->sut->execute($observerMock);
    }
}
