<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Observer\ProductObserver;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Framework\Event\Observer as EventObserver;
use PHPUnit\Framework\MockObject\MockObject;

class ProductObserverTest extends BaseTestCase
{
    /** @var ApiService|MockObject */
    private $apiServiceMock;
    /** @var ProductRepositoryInterface|MockObject */
    private $productRepositoryMock;
    /** @var ProductObserver */
    private $sut;

    protected function setUp(): void
    {
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockWithoutConstructing(Logger::class);
        $this->apiServiceMock = $this->getMockWithoutConstructing(ApiService::class);
        $this->productRepositoryMock = $this->getMockWithoutConstructing(ProductRepositoryInterface::class);

        $this->sut = new ProductObserver(
            $loggerMock,
            $this->apiServiceMock,
            $this->productRepositoryMock
        );
    }

    /**
     * @test
     */
    public function shouldUpsertProductCatalog(): void
    {
        $productId = 2;
        $storeId = 3;

        $productMock = $this->getMockWithoutConstructing(MagentoProduct::class);
        $productMock->method('getStoreIds')->willReturn([$storeId]);
        $productMock->method('getStoreId')->willReturn($storeId);
        $productMock->method('getId')->willReturn($productId);

        $this->productRepositoryMock
            ->expects(self::once())
            ->method('getById')
            ->with($productId, false, $storeId)
            ->willReturn($productMock);

        $observerMock = $this->getMockWithoutConstructing(EventObserver::class, [], ['getProduct']);
        $observerMock->method('getProduct')->willReturn($productMock);

        $this->apiServiceMock
            ->expects(self::once())
            ->method('upsertProductCatalog')
            ->with($productMock, new Scope($storeId));

        $this->sut->execute($observerMock);
    }
}
