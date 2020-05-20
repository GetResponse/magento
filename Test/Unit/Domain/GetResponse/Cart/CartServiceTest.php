<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Cart\CartService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Cart\CartServiceFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Quote\ReadModel\Query\QuoteById;
use GetResponse\GetResponseIntegration\Domain\Magento\Quote\ReadModel\QuoteReadModel;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Product\Product;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Quote\Model\Quote;
use GrShareCode\Cart\CartService as GrCartService;
use PHPUnit\Framework\MockObject\MockObject;

class CartServiceTest extends BaseTestCase
{
    /** @var ProductFactory|MockObject */
    private $productFactory;
    /** @var GrCartService|MockObject */
    private $grCartService;
    /** @var CartHelper|MockObject */
    private $cartHelper;
    /** @var CartService */
    private $sut;
    /** @var QuoteReadModel|MockObject */
    private $quoteReadModel;
    /** @var Scope|MockObject */
    private $scope;

    protected function setUp()
    {
        $this->grCartService = $this->getMockWithoutConstructing(GrCartService::class);
        $this->cartHelper = $this->getMockWithoutConstructing(CartHelper::class);
        /** @var CartServiceFactory $cartServiceFactory */
        $cartServiceFactory = $this->getMockWithoutConstructing(CartServiceFactory::class);
        $cartServiceFactory
            ->method('create')
            ->willReturn($this->grCartService);

        $this->productFactory = $this->getMockWithoutConstructing(ProductFactory::class);
        $this->quoteReadModel = $this->getMockWithoutConstructing(QuoteReadModel::class);
        $this->scope = $this->getMockWithoutConstructing(Scope::class);

        $this->sut = new CartService(
            $cartServiceFactory,
            $this->productFactory,
            $this->cartHelper,
            $this->quoteReadModel
        );
    }

    /**
     * @test
     */
    public function shouldSendCart()
    {
        $quoteId = '1';

        $product = $this->getMockWithoutConstructing(Product::class);

        $product->expects(self::once())
            ->method('getVariants')
            ->willReturn([]);

        /** @var Quote|MockObject $quote */
        $quote = $this->getMockWithoutConstructing(Quote::class);

        $quote->expects(self::once())
            ->method('getId')
            ->willReturn($quoteId);

        $quote->expects(self::exactly(3))
            ->method('__call')
            ->withConsecutive(['getQuoteCurrencyCode'], ['getGrandTotal'], ['getCustomerEmail'])
            ->willReturnOnConsecutiveCalls('PLN', 123, 'jan.kowalski@getresponse.com');

        /** @var Quote\Item $quoteItem */
        $quoteItem = $this->getMockWithoutConstructing(Quote\Item::class);

        $quote->expects(self::once())
            ->method('getAllVisibleItems')
            ->willReturn([$quoteItem]);

        $this->productFactory
            ->expects(self::once())
            ->method('fromMagentoQuoteItem')
            ->with($quoteItem)
            ->willReturn($product);

        $this->quoteReadModel
            ->expects(self::once())
            ->method('getQuoteById')
            ->with(new QuoteById($quoteId))
            ->willReturn($quote);

        $this->cartHelper
            ->expects(self::once())
            ->method('getCartUrl')
            ->willReturn('https://my_magento_shop.com/checkout/cart/');

        $this->grCartService
            ->expects(self::once())
            ->method('sendCart');

        $this->sut->sendCart(
            $quoteId,
            'contactListId',
            'grShopId',
            $this->scope
        );
    }
}