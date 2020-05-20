<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Variant;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductUrlFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant\ComplexVariantFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\ProductReadModel;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Product\Variant\VariantsCollection;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use PHPUnit\Framework\MockObject\MockObject;

class VariantsFactoryComplexTest extends BaseTestCase
{
    /** @var ProductUrlFactory|MockObject */
    private $productUrlFactory;

    /** @var ComplexVariantFactory */
    private $variantFactoryComplex;
    /** @var ProductReadModel|MockObject */
    private $productReadModel;

    protected function setUp()
    {
        $this->productUrlFactory = $this->getMockWithoutConstructing(ProductUrlFactory::class);
        $this->productReadModel = $this->getMockWithoutConstructing(ProductReadModel::class);

        $this->variantFactoryComplex = new ComplexVariantFactory(
            $this->productUrlFactory,
            $this->productReadModel
        );
    }

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

        $childQuoteItems = [$childQuoteItem, $childQuoteItem];

        $quoteItem = $this->getMockWithoutConstructing(QuoteItem::class);

        $this->productReadModel
            ->expects(self::exactly(count($childQuoteItems)))
            ->method('getProduct')
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
        $this->assertEquals(120, $magentoVariant->getPrice());
        $this->assertEquals(100, $magentoVariant->getPriceTax());
        $this->assertEquals('Product SKU No', $magentoVariant->getSku());
        $this->assertEquals('http://getresponse.com', $magentoVariant->getUrl());
        $this->assertEquals('Product short description', $magentoVariant->getDescription());
    }

    /**
     * @return Product|MockObject
     */
    private function getMagentoVariantMock()
    {
        $magentoVariant = $this->getMockWithoutConstructing(Product::class, [
            'getValue',
            'getBaseAmount',
            'getAmount',
            'getPrice',
            'getPriceInfo',
            'getSku',
            'getName',
            'getId',
            '__call',
            'getMediaGalleryImages'
        ]);
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

        $magentoVariant->expects(self::exactly(2))
            ->method('getPriceInfo')
            ->will($this->returnSelf());

        $magentoVariant->expects(self::exactly(2))
            ->method('getPrice')
            ->with('final_price')
            ->will($this->returnSelf());

        $magentoVariant->expects(self::exactly(2))
            ->method('getAmount')
            ->will($this->returnSelf());

        $magentoVariant->expects(self::exactly(2))
            ->method('getBaseAmount')
            ->willReturn(120);

        $magentoVariant->expects(self::exactly(2))
            ->method('getValue')
            ->willReturn(100);

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

        $childQuoteItems = [$childOrderItem, $childOrderItem];

        $orderItem = $this->getMockWithoutConstructing(OrderItem::class);

        $this->productReadModel
            ->expects(self::exactly(count($childQuoteItems)))
            ->method('getProduct')
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
        self::assertInstanceOf(VariantsCollection::class, $magentoVariantCollection);
        $magentoVariant = $magentoVariantCollection->getIterator()[0];

        $this->assertCount(2, $magentoVariantCollection);
        $this->assertEquals(1, $magentoVariant->getExternalId());
        $this->assertEquals('Variant Name', $magentoVariant->getName());
        $this->assertEquals(120, $magentoVariant->getPrice());
        $this->assertEquals(100, $magentoVariant->getPriceTax());
        $this->assertEquals('Product SKU No', $magentoVariant->getSku());
        $this->assertEquals('http://getresponse.com', $magentoVariant->getUrl());
        $this->assertEquals('Product short description', $magentoVariant->getDescription());
    }
}
