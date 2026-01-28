<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Application\GetResponse\TrackingCode\CartService as TrackingCodeCartService;
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
    /** @var ApiService|MockObject */
    private $apiServiceMock;
    /** @var TrackingCodeCartService|MockObject */
    private $trackingCodeCartServiceMock;
    /** @var CartObserver */
    private $sut;

    protected function setUp(): void
    {
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockWithoutConstructing(Logger::class);
        $this->apiServiceMock = $this->getMockWithoutConstructing(ApiService::class);
        $this->trackingCodeCartServiceMock = $this->getMockWithoutConstructing(TrackingCodeCartService::class);

        $this->sut = new CartObserver(
            $loggerMock,
            $this->apiServiceMock,
            $this->trackingCodeCartServiceMock
        );
    }

    /**
     * @test
     */
    public function shouldCreateCart(): void
    {
        $storeId = 3;
        $scope = Scope::createFromStoreId($storeId);

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
}
