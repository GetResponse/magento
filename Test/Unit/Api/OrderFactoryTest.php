<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\AddressFactory;
use GetResponse\GetResponseIntegration\Api\Customer;
use GetResponse\GetResponseIntegration\Api\CustomerFactory;
use GetResponse\GetResponseIntegration\Api\Line;
use GetResponse\GetResponseIntegration\Api\Order;
use GetResponse\GetResponseIntegration\Api\OrderFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GetResponse\GetResponseIntegration\Test\Unit\ApiFaker;
use Magento\Catalog\Model\Product;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Sales\Model\Order as MagentoOrder;

class OrderFactoryTest extends BaseTestCase
{
    /** @var CustomerFactory|MockObject */
    private $customerFactoryMock;
    /** @var AddressFactory|MockObject */
    private $addressFactoryMock;
    /** @var OrderFactory */
    private $sut;

    protected function setUp(): void
    {
        $this->customerFactoryMock = $this->getMockWithoutConstructing(CustomerFactory::class);
        $this->addressFactoryMock = $this->getMockWithoutConstructing(AddressFactory::class);
        $this->sut = new OrderFactory($this->customerFactoryMock, $this->addressFactoryMock);
    }

    /**
     * @test
     */
    public function shouldCreateOrder(): void
    {
        $orderId = 10001;
        $orderNumber = 'order_100001';
        $cartId = 10030;
        $contactEmail = 'some@example.com';
        $totalPrice = 9.99;
        $totalPriceTax = 12.22;
        $shippingPrice = 5;
        $currency = 'EUR';
        $status = 'pending';
        $createdAt = '2021-05-12 23:01:22';
        $updatedAt = '2021-05-12 23:05:40';

        $customerId = 3002;
        $customerEmail = 'some@example2.com';
        $customerFirstName = 'John';
        $customerLastName = 'Smith';
        $customerIsMarketingAccepted = true;
        $customerTags = ['magento', 'api'];
        $customerCustomFields = ['source' => 'api'];

        $line = new Line(40003, 9.99, 12.22, 40, 'variant_40003');

        $address = ApiFaker::createAddress();

        $customer = new Customer(
            $customerId,
            $customerEmail,
            $customerFirstName,
            $customerLastName,
            $customerIsMarketingAccepted,
            $address,
            $customerTags,
            $customerCustomFields
        );

        $this->customerFactoryMock->method('createFromOrder')->willReturn($customer);
        $this->addressFactoryMock->method('createFromOrder')->willReturn($address);

        $orderAddressMock = $this->getMockWithoutConstructing(OrderAddressInterface::class);

        /** @var MagentoOrder|MockObject $magentoOrderMock */
        $magentoOrderMock = $this->getMockWithoutConstructing(MagentoOrder::class);
        $magentoOrderMock->method('getShippingAddress')->willReturn($orderAddressMock);
        $magentoOrderMock->method('getBillingAddress')->willReturn($orderAddressMock);

        $magentoOrderMock->method('getId')->willReturn($orderId);
        $magentoOrderMock->method('getIncrementId')->willReturn($orderNumber);
        $magentoOrderMock->method('getQuoteId')->willReturn($cartId);
        $magentoOrderMock->method('getCustomerEmail')->willReturn($contactEmail);
        $magentoOrderMock->method('getSubtotal')->willReturn($totalPrice);
        $magentoOrderMock->method('getGrandTotal')->willReturn($totalPriceTax);
        $magentoOrderMock->method('getShippingAmount')->willReturn($shippingPrice);
        $magentoOrderMock->method('getOrderCurrencyCode')->willReturn($currency);
        $magentoOrderMock->method('getStatus')->willReturn($status);
        $magentoOrderMock->method('getCreatedAt')->willReturn($createdAt);
        $magentoOrderMock->method('getUpdatedAt')->willReturn($updatedAt);

        $itemMock = $this->getMockWithoutConstructing(
            Item::class,
            ['getProductId', 'getPrice', 'getPriceInclTax', 'getQtyOrdered', 'getSku'],
            ['getChildren']
        );

        $itemMock->method('getChildren')->willReturn([]);
        $itemMock->method('getProductId')->willReturn($line->getVariantId());
        $itemMock->method('getPrice')->willReturn($line->getPrice());
        $itemMock->method('getPriceInclTax')->willReturn($line->getPriceTax());
        $itemMock->method('getQtyOrdered')->willReturn($line->getQuantity());
        $itemMock->method('getSku')->willReturn($line->getSku());

        $magentoOrderMock->method('getAllVisibleItems')->willReturn([$itemMock]);

        $expectedOrder = new Order(
            $orderId,
            $orderNumber,
            $cartId,
            $contactEmail,
            $customer,
            [$line],
            null,
            $totalPrice,
            $totalPriceTax,
            $shippingPrice,
            $currency,
            $status,
            $status,
            $address,
            $address,
            $createdAt,
            $updatedAt
        );

        $order = $this->sut->create($magentoOrderMock);
        self::assertEquals($expectedOrder, $order);
    }
}
