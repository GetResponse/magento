<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Test\Unit\ApiFaker;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer as MagentoCustomer;
use Magento\Quote\Model\Quote;

class OrderFactoryMock
{
    /**
     * @test
     */
    public function shouldCreateProduct(): void
    {
        $product = ApiFaker::createProduct();

        $scope = new Scope(1);
        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_PRODUCT);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock
            ->method('getId')
            ->willReturn($product->getId());
        $productMock
            ->method('getName')
            ->willReturn($product->getName());
        $productMock
            ->method('getTypeId')
            ->willReturn($product->getType());
        $productMock
            ->method('getCreatedAt')
            ->willReturn($product->getCreatedAt());
        $productMock
            ->method('getUpdatedAt')
            ->willReturn($product->getUpdatedAt());

        $this->productFactory
            ->expects(self::once())
            ->method('create')
            ->with($productMock, $scope)
            ->willReturn([$product]);

        $this->httpClientMock
            ->expects(self::once())
            ->method('post')
            ->with($liveSynchronization->getCallbackUrl(), $product);

        $this->sut->upsertProductCatalog($productMock, $scope);
    }
}
