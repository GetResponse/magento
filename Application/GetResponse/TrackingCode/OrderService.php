<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Application\GetResponse\TrackingCode;

use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model\OrderFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\TrackingCodeBufferService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Sales\Model\Order;

class OrderService
{
    /** @var OrderFactory */
    private $orderFactory;
    /** @var TrackingCodeBufferService */
    private $service;
    /** @var Repository */
    private $repository;

    /**
     * @param OrderFactory $orderFactory
     * @param TrackingCodeBufferService $service
     * @param Repository $repository
     */
    public function __construct(OrderFactory $orderFactory, TrackingCodeBufferService $service, Repository $repository)
    {
        $this->orderFactory = $orderFactory;
        $this->service = $service;
        $this->repository = $repository;
    }

    /**
     * Handle add to buffer.
     *
     * @param Order $magentoOrder
     * @param Scope $scope
     */
    public function addToBuffer(Order $magentoOrder, Scope $scope): void
    {
        $webConnect = WebEventTracking::createFromRepository(
            $this->repository->getWebEventTracking($scope->getScopeId())
        );

        if (!$webConnect->isActive()) {
            return;
        }

        $order = $this->orderFactory->create($magentoOrder, $webConnect->getGetresponseShopId());
        $this->service->addOrderToBuffer($order);
    }

    /**
     * Get order from buffer.
     *
     * @param Scope $scope
     */
    public function getOrderFromBuffer(Scope $scope): array
    {
        $webConnect = WebEventTracking::createFromRepository(
            $this->repository->getWebEventTracking($scope->getScopeId())
        );

        if (!$webConnect->isActive()) {
            return [];
        }

        return $this->service->getOrderFromBuffer();
    }
}
