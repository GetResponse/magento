<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Application\GetResponse\TrackingCode\CartService as TrackingCodeCartService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Cart\CartService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\ContactReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Ecommerce\ReadModel\EcommerceReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Observer\CartObserver;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;

class CartObserverTest extends BaseTestCase
{
    /** @var ApiService&MockObject */
    private $apiServiceMock;
    /** @var Repository&MockObject */
    private $repositoryMock;
    /** @var TrackingCodeCartService&MockObject */
    private $trackingCodeCartServiceMock;
    /** @var CartObserver */
    private $sut;

    protected function setUp(): void
    {
        $sessionMock = $this->getMockWithoutConstructing(Session::class);
        /** @var CartService|MockObject $cartServiceMock */
        $cartServiceMock = $this->getMockWithoutConstructing(CartService::class);
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockWithoutConstructing(Logger::class);
        /** @var EcommerceReadModel|MockObject $ecommerceReadModelMock */
        $ecommerceReadModelMock = $this->getMockWithoutConstructing(EcommerceReadModel::class);
        /** @var ContactReadModel|MockObject $contactReadModelMock */
        $contactReadModelMock = $this->getMockWithoutConstructing(ContactReadModel::class);
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);
        $this->apiServiceMock = $this->getMockWithoutConstructing(ApiService::class);
        $this->trackingCodeCartServiceMock = $this->getMockWithoutConstructing(TrackingCodeCartService::class);

        $this->sut = new CartObserver(
            $cartServiceMock,
            $loggerMock,
            $ecommerceReadModelMock,
            $contactReadModelMock,
            $this->repositoryMock,
            $this->apiServiceMock,
            $this->trackingCodeCartServiceMock,
            $sessionMock,
            $this->trackingCodeCartServiceMock
        );
    }

    /**
     * @test
     */
    public function shouldCreateCart(): void
    {
        $storeId = 3;
        $scope = new Scope($storeId);

        /** @var Quote|MockObject $quoteMock */
        $quoteMock = $this->getMockWithoutConstructing(Quote::class);
        $quoteMock->method('getStoreId')->willReturn($storeId);
        /** @var Cart|MockObject $cartMock */
        $cartMock = $this->getMockWithoutConstructing(Cart::class);
        $cartMock->method('getQuote')->willReturn($quoteMock);
        /** @var EventObserver|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(
            EventObserver::class,
            [],
            ['getCart']
        );

        $observerMock->method('getCart')->willReturn($cartMock);

        $this->repositoryMock->expects(self::once())->method('getPluginMode')->willReturn(PluginMode::MODE_NEW);

        $this->apiServiceMock
            ->expects(self::once())
            ->method('createCart')
            ->with($quoteMock, $scope);

        $this->trackingCodeCartServiceMock
            ->expects(self::once())
            ->method('addToBuffer')
            ->with($quoteMock, $scope);

        $this->sut->execute($observerMock);
    }

    /**
     * @test
     */
    public function shouldNotCreateCartWhenOldPluginVersion(): void
    {
        $storeId = 3;

        /** @var Quote|MockObject $quoteMock */
        $quoteMock = $this->getMockWithoutConstructing(Quote::class);
        $quoteMock->method('getStoreId')->willReturn($storeId);
        /** @var Cart|MockObject $cartMock */
        $cartMock = $this->getMockWithoutConstructing(Cart::class);
        $cartMock->method('getQuote')->willReturn($quoteMock);
        /** @var EventObserver|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(EventObserver::class, [], ['getCart']);
        $observerMock->method('getCart')->willReturn($cartMock);

        $this->repositoryMock->expects(self::once())->method('getPluginMode')->willReturn(PluginMode::MODE_OLD);

        $this->apiServiceMock
            ->expects(self::never())
            ->method('createCart');

        $this->trackingCodeCartServiceMock
            ->expects(self::never())
            ->method('addToBuffer');

        $this->sut->execute($observerMock);
    }

    /**
     * @test
     */
    public function shouldNotCreateCartWhenNotLoggedIn(): void
    {
        $storeId = 3;

        /** @var Quote|MockObject $quoteMock */
        $quoteMock = $this->getMockWithoutConstructing(Quote::class);
        $quoteMock->method('getStoreId')->willReturn($storeId);
        /** @var Cart|MockObject $cartMock */
        $cartMock = $this->getMockWithoutConstructing(Cart::class);
        $cartMock->method('getQuote')->willReturn($quoteMock);
        /** @var EventObserver|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(EventObserver::class, [], ['getCart']);
        $observerMock->method('getCart')->willReturn($cartMock);

        $this->repositoryMock->expects(self::never())->method('getPluginMode')->willReturn(PluginMode::MODE_NEW);

        $this->apiServiceMock
            ->expects(self::never())
            ->method('createCart');

        $this->trackingCodeCartServiceMock
            ->expects(self::never())
            ->method('addToBuffer');

        $this->sut->execute($observerMock);
    }


}
