<?php

namespace Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderServiceFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Order\AddOrderCommand;
use GrShareCode\Order\OrderService as GrOrderService;

/**
 * Class OrderServiceTest
 * @package Domain\GetResponse\Order
 */
class OrderServiceTest extends BaseTestCase
{
    /** @var GrOrderService|\PHPUnit_Framework_MockObject_MockObject */
    private $grOrderServiceMock;

    /** @var OrderServiceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $orderServiceFactoryMock;

    public function setUp()
    {
        $this->grOrderServiceMock = $this->getMockWithoutConstructing(GrOrderService::class);
        $this->orderServiceFactoryMock = $this->getMockWithoutConstructing(OrderServiceFactory::class);
    }

    /**
     * @test
     */
    public function shouldSendOrderTest()
    {
        $this->orderServiceFactoryMock->expects($this->once())->method('create')->willReturn($this->grOrderServiceMock);

        /** @var AddOrderCommand|\PHPUnit_Framework_MockObject_MockObject $addOrderCommand */
        $addOrderCommand = $this->getMockWithoutConstructing(AddOrderCommand::class);

        $orderService = new OrderService($this->orderServiceFactoryMock);
        $orderService->sendOrder($addOrderCommand);
    }

    /**
     * @test
     */
    public function shouldExportOrderTest()
    {
        $this->orderServiceFactoryMock->expects($this->once())->method('create')->willReturn($this->grOrderServiceMock);

        /** @var AddOrderCommand|\PHPUnit_Framework_MockObject_MockObject $addOrderCommand */
        $addOrderCommand = $this->getMockWithoutConstructing(AddOrderCommand::class);
        $addOrderCommand->expects($this->once())->method('setToSkipAutomation');

        $orderService = new OrderService($this->orderServiceFactoryMock);
        $orderService->exportOrder($addOrderCommand);
    }
}
