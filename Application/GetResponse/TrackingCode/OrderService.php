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
    private $orderFactory;
    private $service;
    private $repository;

    public function __construct(OrderFactory $orderFactory, TrackingCodeBufferService $service, Repository $repository)
    {
        $this->orderFactory = $orderFactory;
        $this->service = $service;
        $this->repository = $repository;
    }

    public function addToBuffer(Order $magentoOrder, Scope $scope): void
    {
        $webConnect = WebEventTracking::createFromRepository(
            $this->repository->getWebEventTracking($scope->getScopeId())
        );

        if(!$webConnect->isActive()) {
            return;
        }

        $order = $this->orderFactory->create($magentoOrder);
        $this->service->addOrderToBuffer($order);
    }

    public function getOrderFromBuffer(Scope $scope): array
    {
        $webConnect = WebEventTracking::createFromRepository(
            $this->repository->getWebEventTracking($scope->getScopeId())
        );

        if(!$webConnect->isActive()) {
            return [];
        }

        return $this->service->getOrderFromBuffer();
    }
}
