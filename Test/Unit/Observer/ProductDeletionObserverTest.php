<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Tests\Unit\Observer;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Observer\ProductDeletionObserver;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Exception;

class ProductDeletionObserverTest extends TestCase
{
    /** @var Logger|MockObject */
    private $loggerMock;

    /** @var ApiService|MockObject */
    private $apiServiceMock;

    /** @var Repository|MockObject */
    private $repositoryMock;

    /** @var ProductDeletionObserver */
    private $observer;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(Logger::class);
        $this->apiServiceMock = $this->createMock(ApiService::class);
        $this->repositoryMock = $this->createMock(Repository::class);

        $this->observer = new ProductDeletionObserver(
            $this->loggerMock,
            $this->apiServiceMock,
            $this->repositoryMock
        );
    }

    public function testExecuteWhenPluginModeIsNotNewVersion()
    {
        $observerMock = $this->createMock(Observer::class);

        $this->repositoryMock->expects($this->once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_OLD);

        $this->apiServiceMock->expects($this->never())
            ->method('deleteProduct');

        $result = $this->observer->execute($observerMock);
        $this->assertSame($this->observer, $result);
    }

    public function testExecuteWithMultipleStores()
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

        $this->repositoryMock->expects($this->once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_NEW);

        $this->apiServiceMock->expects($this->exactly(3))
            ->method('deleteProduct')
            ->withConsecutive(
                [$productMock, new Scope(1)],
                [$productMock, new Scope(2)],
                [$productMock, new Scope(3)]
            );

        $result = $this->observer->execute($observerMock);
        $this->assertSame($this->observer, $result);
    }

    public function testExecuteWithExceptionHandling()
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

        $this->repositoryMock->expects($this->once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_NEW);

        $this->loggerMock->expects($this->once())
            ->method('addError')
            ->with($exception->getMessage(), ['exception' => $exception]);

        $result = $this->observer->execute($observerMock);
        $this->assertSame($this->observer, $result);
    }
}
