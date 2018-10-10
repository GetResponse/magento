<?php

namespace Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\AddOrderCommandFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Address\AddressFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderServiceFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GetResponse\GetResponseIntegration\Test\Unit\Generator;
use GrShareCode\Order\AddOrderCommand;
use GrShareCode\Order\OrderService as GrOrderService;
use GrShareCode\Product\ProductsCollection;
use Magento\Sales\Model\Order;
use GrShareCode\Order\Order as GrOrder;

/**
 * Class OrderServiceFactoryTest
 * @package Domain\GetResponse\Order
 */
class AddOrderCommandFactoryTest extends BaseTestCase
{
    /** @var GrOrderService|\PHPUnit_Framework_MockObject_MockObject */
    private $grOrderServiceMock;

    /** @var OrderServiceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $orderServiceFactoryMock;

    /** @var ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $productFactoryMock;

    /** @var AddressFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $addressFactoryMock;

    /** @var Order|\PHPUnit_Framework_MockObject_MockObject */
    private $orderMock;

    public function setUp()
    {
        $this->grOrderServiceMock = $this->getMockWithoutConstructing(GrOrderService::class);
        $this->orderMock = $this->getMockWithoutConstructing(Order::class);
        $this->orderServiceFactoryMock = $this->getMockWithoutConstructing(OrderServiceFactory::class);
        $this->productFactoryMock = $this->getMockWithoutConstructing(ProductFactory::class);
        $this->addressFactoryMock = $this->getMockWithoutConstructing(AddressFactory::class);
    }

    /**
     * @test
     */
    public function shouldCreateValidCommand()
    {
        $email = 'test@test.com';
        $contactListId = 'Xdk3';
        $shopId = 'e93D';
        $orderId = '10000045';
        $totalPrice = 80.00;
        $totalPriceTax = 99.99;
        $currency = 'PLN';
        $status = 'pending';
        $cartId = '2000034';
        $shippingAmount = 20.00;
        $createdAt = '2018-09-22 12:01:01';

        $grOrder = new GrOrder(
            $orderId,
            new ProductsCollection(),
            $totalPrice,
            $totalPriceTax,
            '',
            $currency,
            $status,
            $cartId,
            '',
            $shippingAmount,
            '',
            '2018-09-22T12:01:01+0000',
            Generator::createAddress(),
            Generator::createAddress()
        );

        $this->orderMock->expects($this->once())->method('getId')->willReturn($orderId);
        $this->orderMock->expects($this->once())->method('getBaseSubtotal')->willReturn($totalPrice);
        $this->orderMock->expects($this->once())->method('getTaxAmount')->willReturn($totalPriceTax);
        $this->orderMock->expects($this->once())->method('getOrderCurrencyCode')->willReturn($currency);
        $this->orderMock->expects($this->once())->method('getStatus')->willReturn($status);
        $this->orderMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->orderMock->expects($this->once())->method('getShippingAmount')->willReturn($shippingAmount);
        $this->orderMock->expects($this->once())->method('getCreatedAt')->willReturn($createdAt);
        $this->orderMock->expects($this->once())->method('getCustomerEmail')->willReturn($email);
        $this->orderMock->expects($this->once())->method('getAllVisibleItems')->willReturn([]);
        $this->orderMock->expects($this->once())->method('getCreatedAt')->willReturn('2018-09-22 12:01:01');

        $this->addressFactoryMock->expects($this->once())->method('createShippingAddressFromMagentoOrder')->willReturn(Generator::createAddress());
        $this->addressFactoryMock->expects($this->once())->method('createBillingAddressFromMagentoOrder')->willReturn(Generator::createAddress());

        $expectedCommand = new AddOrderCommand(
            $grOrder,
            $email,
            $contactListId,
            $shopId
        );

        $addOrderCommandFactory = new AddOrderCommandFactory(
            $this->productFactoryMock,
            $this->addressFactoryMock
        );

        $addOrderCommand = $addOrderCommandFactory->createForOrderService(
            $this->orderMock,
            $contactListId,
            $shopId
        );

        $this->assertInstanceOf(AddOrderCommand::class, $addOrderCommand);
        $this->assertEquals($expectedCommand, $addOrderCommand);
    }
}
