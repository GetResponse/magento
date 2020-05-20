<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Command\AddOrderCommandFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Order\Order as GrOrder;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;

class AddOrderCommandFactoryTest extends BaseTestCase
{
    /** @var OrderFactory|MockObject */
    private $orderFactory;

    /** @var AddOrderCommandFactory */
    private $addOrderCommandFactory;

    public function setUp()
    {
        $this->orderFactory = $this->getMockWithoutConstructing(OrderFactory::class);
        $this->addOrderCommandFactory = new AddOrderCommandFactory($this->orderFactory);
    }

    /**
     * @test
     */
    public function shouldCreateValidCommand()
    {
        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockWithoutConstructing(Order::class);
        $grOrder = $this->getMockWithoutConstructing(GrOrder::class);

        $orderMock
            ->expects(self::once())
            ->method('getCustomerEmail')
            ->willReturn('adam.test@getresponse.com');

        $this->orderFactory
            ->expects(self::once())
            ->method('fromMagentoOrder')
            ->with($orderMock)
            ->willReturn($grOrder);

        $this->addOrderCommandFactory->createForMagentoOrder(
            $orderMock,
            'contactListId',
            'shopId'
        );
    }
}
