<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Tests\Unit\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Observer\ProductDeletedObserver;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductDeletedObserverTest extends TestCase
{
    /** @var Logger&MockObject */
    private $loggerMock;

    /** @var ApiService&MockObject */
    private $apiServiceMock;

    /** @var ProductDeletedObserver */
    private $observer;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(Logger::class);
        $this->apiServiceMock = $this->createMock(ApiService::class);

        $this->observer = new ProductDeletedObserver($this->loggerMock, $this->apiServiceMock);
    }

    public function testExecuteWithMultipleStores(): void
    {
        $productMock = $this->createMock(Product::class);
        $observerMock = $this->createMock(Observer::class);

        $storeIds = [1, 2, 3];

        $observerMock->expects($this->once())
            ->method('__call')
            ->with('getProduct', [])
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('getWebsiteStoreIds')
            ->willReturn($storeIds);

        $this->apiServiceMock->expects($this->exactly(3))
            ->method('deleteProduct')
            ->withConsecutive(
                [$productMock, Scope::createFromStoreId(1)],
                [$productMock, Scope::createFromStoreId(2)],
                [$productMock, Scope::createFromStoreId(3)]
            );

        $result = $this->observer->execute($observerMock);
        $this->assertSame($this->observer, $result);
    }

    public function testExecuteWithExceptionHandling(): void
    {
        $productMock = $this->createMock(Product::class);
        $observerMock = $this->createMock(Observer::class);
        $exception = new Exception('Test error');

        $observerMock->expects($this->once())
            ->method('__call')
            ->with('getProduct', [])
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('getWebsiteStoreIds')
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('addError')
            ->with($exception->getMessage(), ['exception' => $exception]);

        $result = $this->observer->execute($observerMock);
        $this->assertSame($this->observer, $result);
    }
}
