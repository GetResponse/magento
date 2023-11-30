<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Application\GetResponse\TrackingCode;

use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model\OrderFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\TrackingCodeSession;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Sales\Model\Order;

class OrderService
{
    private $orderFactory;
    private $session;
    private $repository;

    public function __construct(OrderFactory $orderFactory, TrackingCodeSession $session, Repository $repository)
    {
        $this->orderFactory = $orderFactory;
        $this->session = $session;
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
        $this->session->addOrderToBuffer($order);
    }

    public function getOrderFromBuffer(Scope $scope): array
    {
        $webConnect = WebEventTracking::createFromRepository(
            $this->repository->getWebEventTracking($scope->getScopeId())
        );

        if(!$webConnect->isActive()) {
            return [];
        }

        return $this->session->getOrderFromBuffer();
    }
}
