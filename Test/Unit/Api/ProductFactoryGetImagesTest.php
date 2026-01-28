<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use ArrayIterator;
use GetResponse\GetResponseIntegration\Api\Image;
use GetResponse\GetResponseIntegration\Api\ProductFactory;
use GetResponse\GetResponseIntegration\Api\ProductType;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\ProductReadModel;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

/**
 * @covers \GetResponse\GetResponseIntegration\Api\ProductFactory
 */
class ProductFactoryGetImagesTest extends BaseTestCase
{
    /** @var ProductFactory */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductFactory(
            $this->createMock(CategoryRepository::class),
            $this->createMock(ProductReadModel::class),
            $this->createMock(ProductType::class)
        );
    }

    /**
     * @test
     */
    public function shouldReturnEmptyArrayWhenNoImages(): void
    {
        $product = $this->createMock(MagentoProduct::class);
        $mediaGalleryCollection = $this->createMock(AbstractDb::class);

        $mediaGalleryCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator([]));

        $product->expects($this->once())
            ->method('getMediaGalleryImages')
            ->willReturn($mediaGalleryCollection);

        $result = $this->invokePrivateMethod($this->sut, 'getImages', [$product]);

        self::assertEmpty($result);
    }

    /**
     * @test
     */
    public function shouldReturnSingleImageWhenOnlyOneExists(): void
    {
        $product = $this->createMock(MagentoProduct::class);
        $mediaGalleryCollection = $this->createMock(AbstractDb::class);

        $image = $this->createImageMock('http://example.com/single-image.jpg', 0);

        $mediaGalleryCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator([$image]));

        $product->expects($this->once())
            ->method('getMediaGalleryImages')
            ->willReturn($mediaGalleryCollection);

        $result = $this->invokePrivateMethod($this->sut, 'getImages', [$product]);

        self::assertCount(1, $result);
        self::assertInstanceOf(Image::class, $result[0]);
        self::assertEquals('http://example.com/single-image.jpg', $result[0]->getSrc());
        self::assertEquals(0, $result[0]->getPosition());
    }

    /**
     * @test
     */
    public function shouldSortImagesByPositionAndReturnFirst(): void
    {
        $product = $this->createMock(MagentoProduct::class);
        $mediaGalleryCollection = $this->createMock(AbstractDb::class);

        $image1 = $this->createImageMock('http://example.com/image-pos-5.jpg', 5);
        $image2 = $this->createImageMock('http://example.com/image-pos-0.jpg', 0);
        $image3 = $this->createImageMock('http://example.com/image-pos-3.jpg', 3);

        $mediaGalleryCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator([$image1, $image2, $image3]));

        $product->expects($this->once())
            ->method('getMediaGalleryImages')
            ->willReturn($mediaGalleryCollection);

        $result = $this->invokePrivateMethod($this->sut, 'getImages', [$product]);

        self::assertCount(1, $result);
        self::assertEquals('http://example.com/image-pos-0.jpg', $result[0]->getSrc());
        self::assertEquals(0, $result[0]->getPosition());
    }

    /**
     * @test
     */
    public function shouldReturnFirstImageWhenMultipleImagesExist(): void
    {
        $product = $this->createMock(MagentoProduct::class);
        $mediaGalleryCollection = $this->createMock(AbstractDb::class);

        $image1 = $this->createImageMock('http://example.com/image1.jpg', 2);
        $image2 = $this->createImageMock('http://example.com/image2.jpg', 1);
        $image3 = $this->createImageMock('http://example.com/image3.jpg', 3);

        $mediaGalleryCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator([$image1, $image2, $image3]));

        $product->expects($this->once())
            ->method('getMediaGalleryImages')
            ->willReturn($mediaGalleryCollection);

        $result = $this->invokePrivateMethod($this->sut, 'getImages', [$product]);

        self::assertCount(1, $result);
        self::assertInstanceOf(Image::class, $result[0]);
        self::assertEquals('http://example.com/image2.jpg', $result[0]->getSrc());
        self::assertEquals(1, $result[0]->getPosition());
    }

    private function createImageMock(string $url, int $position): MockObject
    {
        $image = $this->createMock(DataObject::class);

        $image->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap([
                ['url', null, $url],
                ['position', null, $position]
            ]);

        return $image;
    }

    private function invokePrivateMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
