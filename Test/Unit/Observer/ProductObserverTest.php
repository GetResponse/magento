<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Observer\ProductObserver;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\Event\Observer as EventObserver;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Catalog\Model\Product as MagentoProduct;

class ProductObserverTest extends BaseTestCase
{
    /** @var Repository|MockObject */
    private $repositoryMock;
    /** @var ApiService|MockObject */
    private $apiServiceMock;
    /** @var ProductObserver */
    private $sut;

    protected function setUp(): void
    {
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockWithoutConstructing(Logger::class);
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);
        $this->apiServiceMock = $this->getMockWithoutConstructing(ApiService::class);

        $this->sut = new ProductObserver(
            $loggerMock,
            $this->repositoryMock,
            $this->apiServiceMock
        );
    }

    /**
     * @test
     */
    public function shouldUpsertProductCatalog(): void
    {
        $storeId = 3;

        $productMock = $this->getMockWithoutConstructing(MagentoProduct::class);
        $productMock->method('getStoreIds')->willReturn([$storeId]);
        $productMock->method('getStoreId')->willReturn($storeId);

        $observerMock = $this->getMockWithoutConstructing(EventObserver::class, [], ['getProduct']);
        $observerMock->method('getProduct')->willReturn($productMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_NEW);

        $this->apiServiceMock
            ->expects(self::once())
            ->method('upsertProductCatalog')
            ->with($productMock, new Scope($storeId));

        $this->sut->execute($observerMock);
    }

    /**
     * @test
     */
    public function shouldNotUpsertProductCatalogWhenOldPluginMode(): void
    {
        $observerMock = $this->getMockWithoutConstructing(EventObserver::class, [], ['getProduct']);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_OLD);

        $this->apiServiceMock
            ->expects(self::never())
            ->method('upsertProductCatalog');

        $this->sut->execute($observerMock);
    }
}
