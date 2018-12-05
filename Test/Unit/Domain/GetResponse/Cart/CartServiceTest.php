<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Cart\CartService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Cart\CartServiceFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Product\Product;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Quote\Model\Quote;
use GrShareCode\Cart\CartService as GrCartService;
/**
 * Class CartServiceTest
 * @package Test\Unit\Domain\GetResponse\Cart
 */
class CartServiceTest extends BaseTestCase
{
    /** @var CartServiceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $cartServiceFactory;

    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $magentoRepository;

    /** @var ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $productFactory;

    /** @var GrCartService|\PHPUnit_Framework_MockObject_MockObject */
    private $grCartService;

    /** @var CartHelper|\PHPUnit_Framework_MockObject_MockObject */
    private $cartHelper;

    /** @var CartService */
    private $sut;

    protected function setUp()
    {
        $this->grCartService = $this->getMockWithoutConstructing(GrCartService::class);
        $this->cartHelper = $this->getMockWithoutConstructing(CartHelper::class);
        $this->cartServiceFactory = $this->getMockWithoutConstructing(CartServiceFactory::class);
        $this->cartServiceFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($this->grCartService);

        $this->magentoRepository = $this->getMockWithoutConstructing(Repository::class);
        $this->productFactory = $this->getMockWithoutConstructing(ProductFactory::class);

        $this->sut = new CartService(
            $this->cartServiceFactory,
            $this->magentoRepository,
            $this->productFactory,
            $this->cartHelper
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

        /** @var Quote|\PHPUnit_Framework_MockObject_MockObject $quote */
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

        $this->magentoRepository
            ->expects(self::once())
            ->method('getQuoteById')
            ->with($quoteId)
            ->willReturn($quote);

        $this->cartHelper
            ->expects(self::once())
            ->method('getCartUrl')
            ->willReturn('https://my_magento_shop.com/checkout/cart/');

        $this->grCartService
            ->expects(self::once())
            ->method('sendCart');

        $this->sut->sendCart($quoteId, 'contactListId', 'grShopId');
    }
}