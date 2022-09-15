<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Product;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductUrlFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\ProductReadModel;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\MockObject\MockObject;

class ProductUrlFactoryTest extends BaseTestCase
{
    /** @var ProductReadModel|MockObject */
    private $productReadModel;

    /** @var ProductUrlFactory */
    private $productUrlFactory;

    protected function setUp(): void
    {
        $this->productReadModel = $this->getMockWithoutConstructing(ProductReadModel::class);
        $this->productUrlFactory = new ProductUrlFactory($this->productReadModel);
    }

    /**
     * @test
     */
    public function shouldReturnUrlForVisibleProduct()
    {
        $productUrl = 'https://getresponse.com';

        $product = $this->getMockWithoutConstructing(Product::class);
        $product->expects(self::once())
            ->method('getVisibility')
            ->willReturn(2);

        $product->expects(self::once())
            ->method('getProductUrl')
            ->willReturn('https://getresponse.com');

        $this->productReadModel
            ->expects(self::never())
            ->method('getProductParent');

        $this->assertEquals($productUrl, $this->productUrlFactory->fromProduct($product));
    }

    /**
     * @test
     */
    public function shouldReturnUrlOfParentProductWheneverParentExist()
    {
        $productUrl = 'https://getresponse.com';
        $productParentIds = [123];

        $product = $this->getMockWithoutConstructing(Product::class);
        $product->expects(self::once())
            ->method('getVisibility')
            ->willReturn(1);

        $productParent = $this->getMockWithoutConstructing(Product::class);
        $productParent->expects(self::once())
            ->method('getProductUrl')
            ->willReturn($productUrl);

        $this->productReadModel
            ->expects(self::once())
            ->method('getProductParent')
            ->willReturn($productParent);

        $this->assertEquals($productUrl, $this->productUrlFactory->fromProduct($product));
    }

    /**
     * @test
     */
    public function shouldNotReturnProductUrl()
    {
        $product = $this->getMockWithoutConstructing(Product::class);
        $product->expects(self::once())
            ->method('getVisibility')
            ->willReturn(1);

        $this->productReadModel
            ->expects(self::once())
            ->method('getProductParent')
            ->willReturn(null);

        $this->assertNull($this->productUrlFactory->fromProduct($product));
    }
}
