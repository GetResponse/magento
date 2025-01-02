<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\CartFactory;
use GetResponse\GetResponseIntegration\Api\CustomerFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GetResponse\GetResponseIntegration\Test\Unit\ApiFaker;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Data\Customer as MagentoCustomer;
use Magento\Quote\Model\Quote;
use GetResponse\GetResponseIntegration\Helper\Cart as CartHelper;
use PHPUnit\Framework\MockObject\MockObject;

class CartFactoryTest extends BaseTestCase
{
    /** @var CartHelper|MockObject */
    private $cartHelperMock;
    /** @var CustomerFactory|MockObject */
    private $customerFactoryMock;
    /** @var CartFactory */
    private $sut;

    protected function setUp(): void
    {
        $this->cartHelperMock = $this->getMockWithoutConstructing(CartHelper::class);
        $this->customerFactoryMock = $this->getMockWithoutConstructing(CustomerFactory::class);
        $this->sut = new CartFactory($this->cartHelperMock, $this->customerFactoryMock);
    }

    /**
     * @test
     */
    public function shouldCreateCart(): void
    {
        $customer = ApiFaker::createCustomer();
        $expectedCart = ApiFaker::createCart();
        $productId = 595949;

        $customerMock = $this->getMockBuilder(MagentoCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock = $this->getMockWithoutConstructing(
            Quote::class,
            ['getId', 'getCustomer', 'getAllVisibleItems', 'getCreatedAt', 'getUpdatedAt'],
            ['getSubtotal', 'getGrandTotal', 'getQuoteCurrencyCode']
        );

        $productMock = $this->getMockWithoutConstructing(Product::class, ['getId']);
        $productMock->method('getId')->willReturn($productId);

        $quantityOptionMock = $this->getMockWithoutConstructing(Quote\Item\Option::class, ['getProduct']);
        $quantityOptionMock->method('getProduct')->willReturn($productMock);

        $itemMock = $this->getMockWithoutConstructing(
            Quote\Item::class,
            ['getQtyOptions', 'getConvertedPrice', 'getTotalQty', 'getSku'],
            ['getPriceInclTax']
        );
        $itemMock->method('getQtyOptions')->willReturn([$quantityOptionMock]);
        $itemMock->method('getConvertedPrice')->willReturn(9.99);
        $itemMock->method('getPriceInclTax')->willReturn(12.99);
        $itemMock->method('getTotalQty')->willReturn(1);
        $itemMock->method('getSku')->willReturn('product-2929');



        $quoteMock->method('getId')->willReturn($expectedCart->getId());
        $quoteMock->method('getCustomer')->willReturn($customerMock);
        $quoteMock->method('getAllVisibleItems')->willReturn([]);
        $quoteMock->method('getSubtotal')->willReturn($expectedCart->getTotalPrice());
        $quoteMock->method('getGrandTotal')->willReturn($expectedCart->getTotalTaxPrice());
        $quoteMock->method('getQuoteCurrencyCode')->willReturn($expectedCart->getCurrency());
        $quoteMock->method('getCreatedAt')->willReturn($expectedCart->getCreatedAt());
        $quoteMock->method('getUpdatedAt')->willReturn($expectedCart->getUpdatedAt());

        $this->cartHelperMock->method('getCartUrl')->willReturn($expectedCart->getUrl());

        $this->customerFactoryMock->method('create')->willReturn($customer);

        $cart = $this->sut->create($quoteMock);

        self::assertEquals($expectedCart, $cart);
    }
}
