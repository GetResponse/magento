<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Variant;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductUrlFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant\VariantsFactoryComplex;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Product\Variant\VariantsCollection;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use PHPUnit_Framework_MockObject_MockObject;

class VariantsFactoryComplexTest extends BaseTestCase
{
    /** @var Repository|PHPUnit_Framework_MockObject_MockObject */
    private $magentoRepository;

    /** @var ProductUrlFactory|PHPUnit_Framework_MockObject_MockObject */
    private $productUrlFactory;

    /** @var VariantsFactoryComplex */
    private $variantFactoryComplex;

    /**
     * @test
     */
    public function shouldReturnVariantCollectionFromQuoteItem()
    {
        $magentoVariant = $this->getMagentoVariantMock();

        $parentProduct = $this->getMockWithoutConstructing(Product::class);
        $product = $this->getMockWithoutConstructing(Product::class);
        $product->expects(self::exactly(2))
            ->method('getId')
            ->willReturn(1);

        $childQuoteItem = $this->getMockWithoutConstructing(QuoteItem::class);
        $childQuoteItem->expects(self::exactly(3))
            ->method('getProduct')
            ->willReturn($product);

        $childQuoteItem->expects(self::exactly(2))
            ->method('getPrice')
            ->willReturn(3);

        $childQuoteItem->expects(self::exactly(2))
            ->method('__call')
            ->with('getPriceInclTax')
            ->willReturn(4);

        $childQuoteItems = [$childQuoteItem, $childQuoteItem];

        $quoteItem = $this->getMockWithoutConstructing(QuoteItem::class);

        $this->magentoRepository
            ->expects(self::exactly(count($childQuoteItems)))
            ->method('getProductById')
            ->willReturn($magentoVariant);

        $quoteItem->expects(self::once())
            ->method('getChildren')
            ->willReturn($childQuoteItems);

        $quoteItem->expects(self::exactly(count($childQuoteItems)))
            ->method('getProduct')
            ->willReturn($parentProduct);

        $this->productUrlFactory
            ->expects(self::exactly(count($childQuoteItems)))
            ->method('fromProduct')
            ->with($childQuoteItem->getProduct())
            ->willReturn('http://getresponse.com');

        $magentoVariantCollection = $this->variantFactoryComplex->fromQuoteItem($quoteItem);
        $this->assertInstanceOf(VariantsCollection::class, $magentoVariantCollection);
        $magentoVariant = $magentoVariantCollection->getIterator()[0];

        $this->assertCount(2, $magentoVariantCollection);
        $this->assertEquals(1, $magentoVariant->getExternalId());
        $this->assertEquals('Variant Name', $magentoVariant->getName());
        $this->assertEquals(3, $magentoVariant->getPrice());
        $this->assertEquals(4, $magentoVariant->getPriceTax());
        $this->assertEquals('Product SKU No', $magentoVariant->getSku());
        $this->assertEquals('http://getresponse.com', $magentoVariant->getUrl());
        $this->assertEquals('Product short description', $magentoVariant->getDescription());
    }

    /**
     * @return Product|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMagentoVariantMock()
    {
        $magentoVariant = $this->getMockWithoutConstructing(Product::class);
        $magentoVariant->expects(self::exactly(2))
            ->method('getId')
            ->willReturn(1);

        $magentoVariant->expects(self::exactly(2))
            ->method('getName')
            ->willReturn('Variant Name');

        $magentoVariant->expects(self::exactly(2))
            ->method('getSku')
            ->willReturn('Product SKU No');

        $magentoVariant->expects(self::exactly(2))
            ->method('__call')
            ->with('getShortDescription')
            ->willReturn('Product short description');

        $magentoVariant->expects(self::exactly(2))
            ->method('getMediaGalleryImages')
            ->willReturn([]);

        return $magentoVariant;
    }

    /**
     * @test
     */
    public function shouldReturnVariantCollectionFromOrderItem()
    {
        $magentoVariant = $this->getMagentoVariantMock();

        $parentProduct = $this->getMockWithoutConstructing(Product::class);
        $product = $this->getMockWithoutConstructing(Product::class);
        $product->expects(self::exactly(2))
            ->method('getId')
            ->willReturn(1);

        $childOrderItem = $this->getMockWithoutConstructing(OrderItem::class);
        $childOrderItem->expects(self::exactly(3))
            ->method('getProduct')
            ->willReturn($product);

        $childOrderItem->expects(self::exactly(2))
            ->method('getPrice')
            ->willReturn(3);

        $childOrderItem->expects(self::exactly(2))
            ->method('getPriceInclTax')
            ->willReturn(4);

        $childQuoteItems = [$childOrderItem, $childOrderItem];

        $orderItem = $this->getMockWithoutConstructing(OrderItem::class);

        $this->magentoRepository
            ->expects(self::exactly(count($childQuoteItems)))
            ->method('getProductById')
            ->willReturn($magentoVariant);

        $orderItem->expects(self::once())
            ->method('getChildrenItems')
            ->willReturn($childQuoteItems);

        $orderItem->expects(self::exactly(count($childQuoteItems)))
            ->method('getProduct')
            ->willReturn($parentProduct);

        $this->productUrlFactory
            ->expects(self::exactly(count($childQuoteItems)))
            ->method('fromProduct')
            ->with($childOrderItem->getProduct())
            ->willReturn('http://getresponse.com');

        $magentoVariantCollection = $this->variantFactoryComplex->fromOrderItem($orderItem);
        $this->assertInstanceOf(VariantsCollection::class, $magentoVariantCollection);
        $magentoVariant = $magentoVariantCollection->getIterator()[0];

        $this->assertCount(2, $magentoVariantCollection);
        $this->assertEquals(1, $magentoVariant->getExternalId());
        $this->assertEquals('Variant Name', $magentoVariant->getName());
        $this->assertEquals(3, $magentoVariant->getPrice());
        $this->assertEquals(4, $magentoVariant->getPriceTax());
        $this->assertEquals('Product SKU No', $magentoVariant->getSku());
        $this->assertEquals('http://getresponse.com', $magentoVariant->getUrl());
        $this->assertEquals('Product short description', $magentoVariant->getDescription());
    }


    protected function setUp()
    {
        $this->magentoRepository = $this->getMockWithoutConstructing(Repository::class);
        $this->productUrlFactory = $this->getMockWithoutConstructing(ProductUrlFactory::class);

        $this->variantFactoryComplex = new VariantsFactoryComplex(
            $this->magentoRepository,
            $this->productUrlFactory
        );
    }
}
