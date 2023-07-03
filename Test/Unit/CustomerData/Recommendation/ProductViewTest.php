<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\CustomerData\Recommendation;

use GetResponse\GetResponseIntegration\CustomerData\Recommendation\ProductView;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Catalog\Block\Product\View as Subject;
use Magento\Catalog\Model\Category;

class ProductViewTest extends BaseTestCase
{
    /** @var StoreManagerInterface|MockObject */
    private $storeManagerMock;
    /** @var Repository|MockObject */
    private $repositoryMock;
    /** @var Http|MockObject */
    private $requestMock;
    /** @var Subject|MockObject */
    private $subjectMock;
    /** @var Product|MockObject */
    private $productMock;
    /** @var CategoryRepositoryInterface|MockObject */
    private $categoryRepositoryMock;

    /** @var ProductView */
    private $sut;

    public function setUp(): void
    {
        $this->storeManagerMock = $this->getMockWithoutConstructing(StoreManagerInterface::class);
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);
        $this->requestMock = $this->getMockWithoutConstructing(Http::class);
        $this->subjectMock = $this->getMockWithoutConstructing(Subject::class, ['getNameInLayout', 'getProduct']);
        $this->productMock = $this->getMockWithoutConstructing(Product::class, ['getTypeId', 'getPrice', 'getSpecialPrice', 'getName', 'getUrlModel', 'getCategoryIds', 'getMediaGalleryImages', 'getStoreId', 'getSku', 'getId', 'isSalable'], ['getDescription']);
        $this->categoryRepositoryMock = $this->getMockWithoutConstructing(CategoryRepositoryInterface::class);

        $this->sut = new ProductView(
            $this->storeManagerMock,
            $this->repositoryMock,
            $this->requestMock,
            $this->categoryRepositoryMock
        );
    }

    /**
     * @test
     */
    public function shouldReturnPayloadWithRecommendationPayload(): void
    {
        $storeId = 1;
        $html = "";
        $productId = 1;
        $productName = 'Test Product';
        $productPrice = 99.99;
        $originalProductPrice = 129.99;
        $description = 'Product description';
        $productSku = 'test-product';
        $categoryName = 'MainCategory';
        $imageUrl = 'https://magento.com/product/1/img/2.jpg';
        $productUrl = 'https://magento.com/product/1.html';

        $storeMock = $this->getMockWithoutConstructing(StoreInterface::class);
        $storeMock->method('getId')->willReturn($storeId);

        $this->storeManagerMock
            ->expects(self::once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_NEW);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getRecommendationSnippet')
            ->with($storeId)
            ->willReturn(['isEnabled' => true, 'codeSnippet' => '']);

        $this->subjectMock
            ->expects(self::once())
            ->method('getNameInLayout')
            ->willReturn(ProductView::DISPLAY_BLOCK);

        $this->subjectMock
            ->expects(self::once())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->requestMock
            ->expects(self::once())
            ->method('getFullActionName')
            ->willReturn(ProductView::FULL_ACTION_NAME);

        $imageMock = $this->getMockWithoutConstructing(DataObject::class);
        $imageMock->method('getData')->with('url')->willReturn($imageUrl);

        $urlModelMock = $this->getMockWithoutConstructing(Product\Url::class);
        $urlModelMock->method('getUrlInStore')->willReturn($productUrl);

        $categoryMock = $this->getMockWithoutConstructing(Category::class);
        $categoryMock->method('getName')->willReturn($categoryName);

        $this->productMock
            ->expects(self::once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->productMock
            ->expects(self::once())
            ->method('getId')
            ->willReturn($productId);

        $this->categoryRepositoryMock
            ->expects(self::once())
            ->method('get')
            ->with(1, $storeId)
            ->willReturn($categoryMock);

        $this->productMock
            ->expects(self::once())
            ->method('getTypeId')
            ->willReturn('');

        $this->productMock
            ->expects(self::once())
            ->method('getSpecialPrice')
            ->willReturn($productPrice);

        $this->productMock
            ->expects(self::once())
            ->method('getPrice')
            ->willReturn($originalProductPrice);

        $this->productMock
            ->expects(self::once())
            ->method('getName')
            ->willReturn($productName);

        $this->productMock
            ->expects(self::once())
            ->method('getUrlModel')
            ->willReturn($urlModelMock);

        $this->productMock
            ->expects(self::once())
            ->method('getDescription')
            ->willReturn($description);

        $this->productMock
            ->expects(self::once())
            ->method('getSku')
            ->willReturn($productSku);

        $this->productMock
            ->expects(self::once())
            ->method('getCategoryIds')
            ->willReturn([1]);

        $this->productMock
            ->expects(self::once())
            ->method('getMediaGalleryImages')
            ->willReturn([$imageMock]);

        $this->productMock
            ->expects(self::once())
            ->method('isSalable')
            ->willReturn(true);

        $expectedPayload = [
            'pageType' => ProductView::PAGE_TYPE,
            'pageData' => [
                'productUrl' => $productUrl,
                'pageUrl' => $productUrl,
                'productExternalId' => (string) $productId,
                'productName' => $productName,
                'price' => number_format($productPrice, 2, '.', ''),
                'imageUrl' => $imageUrl,
                'description' => $description,
                'category' => $categoryName,
                'available' => true,
                'sku' => $productSku,
                'attribute1' => number_format($originalProductPrice, 2, '.', ''),
                'attribute2' => "",
                'attribute3' => "",
                'attribute4' => ""
            ]
        ];

        $expectedHtml = '<script type="text/javascript">const recommendationPayload = ' . json_encode($expectedPayload) . '</script>';

        $result = $this->sut->afterToHtml($this->subjectMock, $html);

        self::assertEquals($expectedHtml, $result);
    }

    /**
     * @test
     */
    public function shouldReturnPayloadWithoutRecommendationPayload(): void
    {
        $storeId = 1;
        $html = "";

        $storeMock = $this->getMockWithoutConstructing(StoreInterface::class);
        $storeMock->method('getId')->willReturn($storeId);

        $this->storeManagerMock
            ->expects(self::once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->storeManagerMock
            ->expects(self::once())
            ->method('getStore')
            ->willReturn($storeId);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_OLD);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getRecommendationSnippet')
            ->with($storeId)
            ->willReturn(['isEnabled' => true, 'codeSnippet' => '']);

        $this->subjectMock
            ->expects(self::never())
            ->method('getNameInLayout')
            ->willReturn(ProductView::DISPLAY_BLOCK);

        $this->requestMock
            ->expects(self::never())
            ->method('getFullActionName')
            ->willReturn(ProductView::FULL_ACTION_NAME);

        $result = $this->sut->afterToHtml($this->subjectMock, $html);

        self::assertEquals($html, $result);
    }
}
