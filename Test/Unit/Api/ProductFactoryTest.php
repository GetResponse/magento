<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\Category;
use GetResponse\GetResponseIntegration\Api\Image;
use GetResponse\GetResponseIntegration\Api\Product;
use GetResponse\GetResponseIntegration\Api\ProductFactory;
use GetResponse\GetResponseIntegration\Api\ProductSalePrice;
use GetResponse\GetResponseIntegration\Api\ProductType;
use GetResponse\GetResponseIntegration\Api\Variant;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\ProductReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\Query\GetProduct;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Catalog\Model\Category as MagentoCategory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product\Url;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Api\ExtensionAttributesInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Catalog\Model\Product as MagentoProduct;

class ProductFactoryTest extends BaseTestCase
{
    /** @var CategoryRepository|MockObject */
    private $categoryRepositoryMock;
    /** @var ProductReadModel|MockObject */
    private $productReadModelMock;
    /** @var ProductFactory */
    private $sut;

    protected function setUp(): void
    {
        $this->categoryRepositoryMock = $this->getMockWithoutConstructing(CategoryRepository::class);
        $this->productReadModelMock = $this->getMockWithoutConstructing(ProductReadModel::class);

        /** @var ProductType|MockObject $productTypeMock */
        $productTypeMock = $this->getMockWithoutConstructing(ProductType::class);
        $productTypeMock->method('isProductConfigurable')->willReturn(false);

        $this->sut = new ProductFactory(
            $this->categoryRepositoryMock,
            $this->productReadModelMock,
            $productTypeMock
        );
    }

    /**
     * @test
     */
    public function shouldCreateActiveProduct(): void
    {
        $scope = new Scope(1);

        $productId = 2002;
        $name = 'TestProduct';
        $type = 'simple';
        $vendor = '';
        $createdAt = '2021-05-01 12:48:54';
        $updatedAt = '2021-05-01 12:49:12';

        $image = new Image('http://store.magento.com/img/1/12/12.jpg', 1);

        $variantSku = 'variant_3001';
        $variantPrice = 9.99;
        $variantPriceTax = $variantPrice;
        $variantPreviousPrice = null;
        $variantPreviousPriceTax = null;
        $variantQty = 99;
        $variantUrl = 'http://store.magento.com/product/2002/variant/3001';
        $variantPosition = 0;
        $variantBarcode = null;
        $variantDescription = 'Test Product - Variant 1 description';
        $variantShortDescription = 'Test Product - Variant 1 short description';
        $variantImages = [$image];

        $categoryId = 13;
        $parentCategoryId = 1;
        $categoryName = 'Default';

        $urlMock = $this->getMockWithoutConstructing(Url::class);
        $urlMock->method('getUrlInStore')->willReturn($variantUrl);

        $mediaMock = $this->getMockWithoutConstructing(MagentoProduct\Image::class);
        $mediaMock->method('getData')
            ->willReturnOnConsecutiveCalls($image->getSrc(), $image->getPosition());

        $stockItemMock = $this->getMockWithoutConstructing(Stock::class, [], ['getQty']);
        $stockItemMock->method('getQty')->willReturn($variantQty);

        $extensionAttributesMock = $this->getMockWithoutConstructing(ExtensionAttributesInterface::class, [], ['getStockItem']);
        $extensionAttributesMock->method('getStockItem')->willReturn($stockItemMock);

        /** @var MagentoProduct|MockObject $magentoProductMock */
        $magentoProductMock = $this->getMockWithoutConstructing(MagentoProduct::class);
        $magentoProductMock->method('getVisibility')->willReturn(MagentoProduct\Visibility::VISIBILITY_BOTH);
        $magentoProductMock->method('getTypeId')->willReturn('simple');
        $magentoProductMock->method('getId')->willReturn($productId);
        $magentoProductMock->method('getName')->willReturn($name);
        $magentoProductMock->method('getSku')->willReturn($variantSku);
        $magentoProductMock->method('getPrice')->willReturn($variantPrice);
        $magentoProductMock->method('setStoreId')->willReturn($magentoProductMock);
        $magentoProductMock->method('getUrlModel')->willReturn($urlMock);
        $magentoProductMock->method('getCategoryIds')->willReturn([$categoryId]);
        $magentoProductMock->method('getData')
            ->willReturnOnConsecutiveCalls($variantDescription, $variantShortDescription);
        $magentoProductMock->method('getMediaGalleryImages')->willReturn([$mediaMock]);
        $magentoProductMock->method('getCreatedAt')->willReturn($createdAt);
        $magentoProductMock->method('getUpdatedAt')->willReturn($updatedAt);
        $magentoProductMock->method('getExtensionAttributes')->willReturn($extensionAttributesMock);
        $magentoProductMock->method('getStatus')->willReturn(1);
        $magentoProductMock->method('getVisibility')->willReturn(2);

        $magentoProductMock->method('getSpecialPrice')->willReturn('8.9900');
        $magentoProductMock->method('getSpecialFromDate')->willReturn('2023-05-01 00:00:00');
        $magentoProductMock->method('getSpecialToDate')->willReturn('2023-06-30 00:00:00');

        $this->productReadModelMock
            ->expects(self::once())
            ->method('getProduct')
            ->with(new GetProduct($productId))
            ->willReturn($magentoProductMock);

        $category = new Category($categoryId, $parentCategoryId, $categoryName);

        $categoryMock = $this->getMockWithoutConstructing(MagentoCategory::class);
        $categoryMock->method('getId')->willReturn($categoryId);
        $categoryMock->method('getParentId')->willReturn($parentCategoryId);
        $categoryMock->method('getName')->willReturn($categoryName);

        $this->categoryRepositoryMock->method('get')->willReturn($categoryMock);

        $productSalePrice = new ProductSalePrice(8.99, '2023-05-01 00:00:00', '2023-06-30 00:00:00');

        $variant = new Variant(
            $productId,
            $name,
            $variantSku,
            $variantPrice,
            $variantPriceTax,
            $variantPreviousPrice,
            $variantPreviousPriceTax,
            $variantQty,
            $variantUrl,
            $variantPosition,
            $variantBarcode,
            $variantDescription,
            $variantShortDescription,
            $variantImages,
            Product::STATUS_PUBLISH,
            $productSalePrice
        );

        $expectedProduct = new Product(
            $productId,
            $name,
            $type,
            $variantUrl,
            $vendor,
            [$category],
            [$variant],
            Product::STATUS_PUBLISH,
            $createdAt,
            $updatedAt
        );

        $products = $this->sut->create($magentoProductMock, $scope);
        self::assertEquals([$expectedProduct], $products);
    }

    /**
     * @test
     */
    public function shouldCreateInactiveProduct(): void
    {
        $scope = new Scope(1);

        $productId = 2002;
        $name = 'TestProduct';
        $type = 'simple';
        $vendor = '';
        $createdAt = '2021-05-01 12:48:54';
        $updatedAt = '2021-05-01 12:49:12';

        $image = new Image('http://store.magento.com/img/1/12/12.jpg', 1);

        $variantSku = 'variant_3001';
        $variantPrice = 9.99;
        $variantPriceTax = $variantPrice;
        $variantPreviousPrice = null;
        $variantPreviousPriceTax = null;
        $variantQty = 99;
        $variantUrl = 'http://store.magento.com/product/2002/variant/3001';
        $variantPosition = 0;
        $variantBarcode = null;
        $variantDescription = 'Test Product - Variant 1 description';
        $variantShortDescription = 'Test Product - Variant 1 short description';
        $variantImages = [$image];

        $categoryId = 13;
        $parentCategoryId = 1;
        $categoryName = 'Default';

        $urlMock = $this->getMockWithoutConstructing(Url::class);
        $urlMock->method('getUrlInStore')->willReturn($variantUrl);

        $mediaMock = $this->getMockWithoutConstructing(MagentoProduct\Image::class);
        $mediaMock->method('getData')
            ->willReturnOnConsecutiveCalls($image->getSrc(), $image->getPosition());

        $stockItemMock = $this->getMockWithoutConstructing(Stock::class, [], ['getQty']);
        $stockItemMock->method('getQty')->willReturn($variantQty);

        $extensionAttributesMock = $this->getMockWithoutConstructing(ExtensionAttributesInterface::class, [], ['getStockItem']);
        $extensionAttributesMock->method('getStockItem')->willReturn($stockItemMock);

        /** @var MagentoProduct|MockObject $magentoProductMock */
        $magentoProductMock = $this->getMockWithoutConstructing(MagentoProduct::class);
        $magentoProductMock->method('getVisibility')->willReturn(MagentoProduct\Visibility::VISIBILITY_BOTH);
        $magentoProductMock->method('getTypeId')->willReturn('simple');
        $magentoProductMock->method('getId')->willReturn($productId);
        $magentoProductMock->method('getName')->willReturn($name);
        $magentoProductMock->method('getSku')->willReturn($variantSku);
        $magentoProductMock->method('getPrice')->willReturn($variantPrice);
        $magentoProductMock->method('setStoreId')->willReturn($magentoProductMock);
        $magentoProductMock->method('getUrlModel')->willReturn($urlMock);
        $magentoProductMock->method('getCategoryIds')->willReturn([$categoryId]);
        $magentoProductMock->method('getData')
            ->willReturnOnConsecutiveCalls($variantDescription, $variantShortDescription);
        $magentoProductMock->method('getMediaGalleryImages')->willReturn([$mediaMock]);
        $magentoProductMock->method('getCreatedAt')->willReturn($createdAt);
        $magentoProductMock->method('getUpdatedAt')->willReturn($updatedAt);
        $magentoProductMock->method('getExtensionAttributes')->willReturn($extensionAttributesMock);
        $magentoProductMock->method('getStatus')->willReturn(2);
        $magentoProductMock->method('getVisibility')->willReturn(1);

        $this->productReadModelMock
            ->expects(self::once())
            ->method('getProduct')
            ->with(new GetProduct($productId))
            ->willReturn($magentoProductMock);

        $category = new Category($categoryId, $parentCategoryId, $categoryName);

        $categoryMock = $this->getMockWithoutConstructing(MagentoCategory::class);
        $categoryMock->method('getId')->willReturn($categoryId);
        $categoryMock->method('getParentId')->willReturn($parentCategoryId);
        $categoryMock->method('getName')->willReturn($categoryName);

        $this->categoryRepositoryMock->method('get')->willReturn($categoryMock);

        $variant = new Variant(
            $productId,
            $name,
            $variantSku,
            $variantPrice,
            $variantPriceTax,
            $variantPreviousPrice,
            $variantPreviousPriceTax,
            $variantQty,
            $variantUrl,
            $variantPosition,
            $variantBarcode,
            $variantDescription,
            $variantShortDescription,
            $variantImages,
            Product::STATUS_DRAFT
        );

        $expectedProduct = new Product(
            $productId,
            $name,
            $type,
            $variantUrl,
            $vendor,
            [$category],
            [$variant],
            Product::STATUS_DRAFT,
            $createdAt,
            $updatedAt
        );

        $products = $this->sut->create($magentoProductMock, $scope);
        self::assertEquals([$expectedProduct], $products);
    }
}
