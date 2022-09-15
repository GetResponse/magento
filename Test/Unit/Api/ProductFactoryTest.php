<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\Category;
use GetResponse\GetResponseIntegration\Api\Image;
use GetResponse\GetResponseIntegration\Api\Product;
use GetResponse\GetResponseIntegration\Api\ProductFactory;
use GetResponse\GetResponseIntegration\Api\ProductType;
use GetResponse\GetResponseIntegration\Api\Variant;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\ProductReadModel;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Catalog\Model\Category as MagentoCategory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product\Url;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Catalog\Model\Product as MagentoProduct;

class ProductFactoryTest extends BaseTestCase
{
    /** @var CategoryRepository|MockObject */
    private $categoryRepositoryMock;
    /** @var StockItemRepository|MockObject */
    private $stockRepositoryMock;
    /** @var ProductReadModel|MockObject */
    private $productReadModelMock;
    /** @var ProductType|MockObject */
    private $productTypeMock;
    /** @var ProductFactory */
    private $sut;

    protected function setUp(): void
    {
        $this->productTypeMock = $this->getMockWithoutConstructing(ProductType::class);
        $this->categoryRepositoryMock = $this->getMockWithoutConstructing(CategoryRepository::class);
        $this->stockRepositoryMock = $this->getMockWithoutConstructing(StockItemRepository::class);
        $this->productReadModelMock = $this->getMockWithoutConstructing(ProductReadModel::class);

        $this->productTypeMock
            ->method('isProductConfigurable')
            ->willReturn(false);

        $this->sut = new ProductFactory(
            $this->categoryRepositoryMock,
            $this->stockRepositoryMock,
            $this->productReadModelMock,
            $this->productTypeMock
        );
    }

    /**
     * @test
     */
    public function shouldCreateProduct(): void
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
        $mediaMock->method('getData')->withConsecutive(['url', null], ['position', null])->willReturnOnConsecutiveCalls($image->getSrc(), $image->getPosition());

        $stockItemMock = $this->getMockWithoutConstructing(Item::class);
        $stockItemMock->method('getQty')->willReturn($variantQty);

        $this->stockRepositoryMock->method('get')->willReturn($stockItemMock);

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
            ->withConsecutive(['description'], ['short_description'])
            ->willReturnOnConsecutiveCalls($variantDescription, $variantShortDescription);
        $magentoProductMock->method('getMediaGalleryImages')->willReturn([$mediaMock]);
        $magentoProductMock->method('getCreatedAt')->willReturn($createdAt);
        $magentoProductMock->method('getUpdatedAt')->willReturn($updatedAt);

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
            $variantImages
        );

        $expectedProduct = new Product(
            $productId,
            $name,
            $type,
            $variantUrl,
            $vendor,
            [$category],
            [$variant],
            $createdAt,
            $updatedAt
        );
//dd($expectedProduct);
        $products = $this->sut->create($magentoProductMock, $scope);
;
        self::assertEquals([$expectedProduct], $products);
    }
}
