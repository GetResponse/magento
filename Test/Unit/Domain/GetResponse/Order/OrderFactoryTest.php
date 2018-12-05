<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Address\AddressFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GetResponse\GetResponseIntegration\Test\Unit\Generator;
use GrShareCode\Order\Order as GrOrder;
use Magento\Sales\Model\Order;

class OrderFactoryTest extends BaseTestCase
{
    /** @var Order|\PHPUnit_Framework_MockObject_MockObject */
    private $magentoOrderMock;

    /** @var ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $productFactory;

    /** @var AddressFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $addressFactory;

    /** @var OrderFactory */
    private $orderFactory;

    public function setUp()
    {
        $this->magentoOrderMock = $this->getMockWithoutConstructing(Order::class);
        $this->productFactory = $this->getMockWithoutConstructing(ProductFactory::class);
        $this->addressFactory = $this->getMockWithoutConstructing(AddressFactory::class);
        $this->orderFactory = new OrderFactory($this->productFactory, $this->addressFactory);
    }

    /**
     * @test
     */
    public function shouldCreateValidOrder()
    {
        $orderItemMock = $this->getMockWithoutConstructing(\Magento\Sales\Model\Order\Item::class);

        $externalOrderId = '100043';
        $totalPrice = 80.00;
        $totalPriceTax = 99.99;
        $orderUrl = null;
        $currency = 'PLN';
        $status = 'pending';
        $externalCardId = '200032';
        $description = null;
        $shippingPrice = 20.00;
        $billingStatus = null;
        $processedAt = '2018-09-22 12:01:01';
        $processedAtISO8601 = '2018-09-22T12:01:01+0000';
        $shippingAddress = $billingAddress = Generator::createAddress();

        $this->magentoOrderMock->method('getId')->willReturn($externalOrderId);
        $this->magentoOrderMock->method('getBaseSubtotal')->willReturn($totalPrice);
        $this->magentoOrderMock->method('getTaxAmount')->willReturn($totalPriceTax);
        $this->magentoOrderMock->method('getOrderCurrencyCode')->willReturn($currency);
        $this->magentoOrderMock->method('getStatus')->willReturn($status);
        $this->magentoOrderMock->method('getQuoteId')->willReturn($externalCardId);
        $this->magentoOrderMock->method('getShippingAmount')->willReturn($shippingPrice);
        $this->magentoOrderMock->method('getCreatedAt')->willReturn($processedAt);
        $this->magentoOrderMock
            ->method('getAllVisibleItems')
            ->willReturn([$orderItemMock, $orderItemMock]);

        $productCollection =  Generator::createProductsCollection(2,1);

        $this->productFactory
            ->expects(self::exactly(2))
            ->method('fromMagentoOrderItem')
            ->willReturnOnConsecutiveCalls($productCollection->getIterator()[0], $productCollection->getIterator()[1]);

        $this->addressFactory
            ->expects(self::once())
            ->method('createShippingAddressFromMagentoOrder')
            ->willReturn($shippingAddress);

        $this->addressFactory
            ->expects(self::once())
            ->method('createBillingAddressFromMagentoOrder')
            ->willReturn($billingAddress);

        $expectedGrOrder = new GrOrder(
            $externalOrderId,
            $totalPrice,
            $currency,
            $productCollection
        );

        $expectedGrOrder
            ->setTotalPriceTax($totalPriceTax)
            ->setStatus($status)
            ->setExternalCartId($externalCardId)
            ->setShippingPrice($shippingPrice)
            ->setProcessedAt($processedAtISO8601)
            ->setShippingAddress($shippingAddress)
            ->setBillingAddress($billingAddress);

        $grOrder = $this->orderFactory->fromMagentoOrder($this->magentoOrderMock);

        $this->assertInstanceOf(GrOrder::class, $grOrder);
        $this->assertEquals($expectedGrOrder, $grOrder);
    }
}
