<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\CartFactory;
use GetResponse\GetResponseIntegration\Api\CustomerFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GetResponse\GetResponseIntegration\Test\Unit\ApiFaker;
use Magento\Customer\Model\Data\Customer as MagentoCustomer;
use Magento\Quote\Model\Quote;
use Magento\Checkout\Helper\Cart as CartHelper;
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

        $customerMock = $this->getMockBuilder(MagentoCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock = $this->getMockWithoutConstructing(
            Quote::class,
            ['getId', 'getCustomer', 'getAllVisibleItems', 'getCreatedAt', 'getUpdatedAt'],
            ['getSubtotal', 'getGrandTotal', 'getQuoteCurrencyCode']
        );

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
