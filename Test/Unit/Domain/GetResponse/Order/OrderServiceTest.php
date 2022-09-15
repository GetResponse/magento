<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderServiceFactory;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Order\Command\AddOrderCommand;
use GrShareCode\Order\OrderService as GrOrderService;
use PHPUnit\Framework\MockObject\MockObject;

class OrderServiceTest extends BaseTestCase
{
    /** @var GrOrderService|MockObject */
    private $grOrderServiceMock;

    /** @var OrderServiceFactory|MockObject */
    private $orderServiceFactoryMock;

    protected function setUp(): void
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

        /** @var AddOrderCommand|MockObject $addOrderCommand */
        $addOrderCommand = $this->getMockWithoutConstructing(AddOrderCommand::class);

        $orderService = new OrderService($this->orderServiceFactoryMock);
        $orderService->addOrder(
            $addOrderCommand,
            $this->getMockWithoutConstructing(Scope::class)
        );
    }
}
