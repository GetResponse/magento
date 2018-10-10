<?php
namespace Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Test\Unit\Generator;
use GrShareCode\Order\Order as GrOrder;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Product\ProductsCollection;
use Magento\Sales\Model\Order;

class OrderFactoryTest extends BaseTestCase
{
    /** @var Order | \PHPUnit_Framework_MockObject_MockObject */
    private $magentoOrderMock;

    public function setUp()
    {
        $this->magentoOrderMock = $this->getMockWithoutConstructing(Order::class);
    }

    /**
     * @test
     */
    public function shouldCreateValidOrder()
    {
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

        $productsCollection = new ProductsCollection();

        $this->magentoOrderMock->method('getId')->willReturn($externalOrderId);
        $this->magentoOrderMock->method('getBaseSubtotal')->willReturn($totalPrice);
        $this->magentoOrderMock->method('getTaxAmount')->willReturn($totalPriceTax);
        $this->magentoOrderMock->method('getOrderCurrencyCode')->willReturn($currency);
        $this->magentoOrderMock->method('getStatus')->willReturn($status);
        $this->magentoOrderMock->method('getQuoteId')->willReturn($externalCardId);
        $this->magentoOrderMock->method('getShippingAmount')->willReturn($shippingPrice);
        $this->magentoOrderMock->method('getCreatedAt')->willReturn($processedAt);

        $expectedGrOrder = new GrOrder(
            $externalOrderId,
            $productsCollection,
            $totalPrice,
            $totalPriceTax,
            $orderUrl,
            $currency,
            $status,
            $externalCardId,
            $description,
            $shippingPrice,
            $billingStatus,
            $processedAtISO8601,
            $shippingAddress,
            $billingAddress
        );

        $grOrder = OrderFactory::fromMagentoOrder(
            $this->magentoOrderMock,
            $productsCollection,
            $shippingAddress,
            $billingAddress
        );

        $this->assertInstanceOf(GrOrder::class, $grOrder);
        $this->assertEquals($expectedGrOrder, $grOrder);
    }
}
