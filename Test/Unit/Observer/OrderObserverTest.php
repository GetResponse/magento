<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Application\GetResponse\TrackingCode\OrderService as TrackingCodeOrderService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\ContactReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Ecommerce\ReadModel\EcommerceReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Command\AddOrderCommandFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Observer\OrderObserver;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Model\Order as MagentoOrder;
use PHPUnit\Framework\MockObject\MockObject;

class OrderObserverTest extends BaseTestCase
{
    /** @var Repository&MockObject */
    private $repositoryMock;
    /** @var ApiService&MockObject */
    private $apiServiceMock;
    /** @var TrackingCodeOrderService&MockObject */
    private $trackingCodeOrderServiceMock;
    /** @var OrderObserver */
    private $sut;

    protected function setUp(): void
    {
        /** @var Session|MockObject $customerSessionMock */
        $customerSessionMock = $this->getMockWithoutConstructing(Session::class);
        /** @var OrderService|MockObject $orderServiceMock */
        $orderServiceMock = $this->getMockWithoutConstructing(OrderService::class);
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockWithoutConstructing(Logger::class);
        /** @var AddOrderCommandFactory|MockObject $addOrderCommandFactoryMock */
        $addOrderCommandFactoryMock = $this->getMockWithoutConstructing(AddOrderCommandFactory::class);
        /** @var EcommerceReadModel|MockObject $ecommerceReadModelMock */
        $ecommerceReadModelMock = $this->getMockWithoutConstructing(EcommerceReadModel::class);
        /** @var ContactReadModel|MockObject $contactReadModelMock */
        $contactReadModelMock = $this->getMockWithoutConstructing(ContactReadModel::class);
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);
        $this->apiServiceMock = $this->getMockWithoutConstructing(ApiService::class);
        $this->trackingCodeOrderServiceMock = $this->getMockWithoutConstructing(TrackingCodeOrderService::class);

        $this->sut = new OrderObserver(
            $customerSessionMock,
            $orderServiceMock,
            $loggerMock,
            $addOrderCommandFactoryMock,
            $ecommerceReadModelMock,
            $contactReadModelMock,
            $this->repositoryMock,
            $this->apiServiceMock,
            $this->trackingCodeOrderServiceMock
        );
    }

    /**
     * @test
     */
    public function shouldCreateOrder(): void
    {
        $storeId = 3;
        $scope = new Scope($storeId);

        $orderMock = $this->getMockWithoutConstructing(MagentoOrder::class);
        $orderMock->method('getStoreId')->willReturn($storeId);

        $observerMock = $this->getMockWithoutConstructing(EventObserver::class, [], ['getOrder']);
        $observerMock->method('getOrder')->willReturn($orderMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_NEW);

        $this->apiServiceMock
            ->expects(self::once())
            ->method('createOrder')
            ->with($orderMock, $scope);

        $this->trackingCodeOrderServiceMock
            ->expects(self::once())
            ->method('addToBuffer')
            ->with($orderMock, $scope);

        $this->sut->execute($observerMock);
    }

    /**
     * @test
     */
    public function shouldNotCreateOrderWhenOldPluginMode(): void
    {
        $storeId = 3;

        $orderMock = $this->getMockWithoutConstructing(MagentoOrder::class);
        $orderMock->method('getStoreId')->willReturn($storeId);

        $observerMock = $this->getMockWithoutConstructing(EventObserver::class, [], ['getOrder']);
        $observerMock->method('getOrder')->willReturn($orderMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_OLD);

        $this->apiServiceMock
            ->expects(self::never())
            ->method('createOrder');

        $this->trackingCodeOrderServiceMock
            ->expects(self::never())
            ->method('addToBuffer');

        $this->sut->execute($observerMock);
    }
}
