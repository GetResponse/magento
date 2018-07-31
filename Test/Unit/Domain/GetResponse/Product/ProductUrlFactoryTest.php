<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Product;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductUrlFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Catalog\Model\Product;

/**
 * Class ProductUrlFactoryTest
 * @package Unit\Domain\GetResponse\Product
 */
class ProductUrlFactoryTest extends BaseTestCase
{
    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $magentoRepository;

    /** @var ProductUrlFactory */
    private $productUrlFactory;

    protected function setUp()
    {
        $this->magentoRepository = $this->getMockWithoutConstructing(Repository::class);
        $this->productUrlFactory = new ProductUrlFactory($this->magentoRepository);
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

        $this->magentoRepository
            ->expects(self::never())
            ->method('getProductParentConfigurableById');

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

        $this->magentoRepository
            ->expects(self::once())
            ->method('getProductParentConfigurableById')
            ->willReturn($productParentIds);

        $this->magentoRepository
            ->expects(self::once())
            ->method('getProductById')
            ->with($productParentIds[0])
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

        $this->magentoRepository
            ->expects(self::once())
            ->method('getProductParentConfigurableById')
            ->willReturn(null);

        $this->assertNull($this->productUrlFactory->fromProduct($product));
    }
}