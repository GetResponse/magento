<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Order\Command\AddOrderCommand;
use GrShareCode\Order\Command\EditOrderCommand;

class OrderService
{
    private $orderServiceFactory;

    public function __construct(OrderServiceFactory $orderServiceFactory)
    {
        $this->orderServiceFactory = $orderServiceFactory;
    }

    /**
     * @param AddOrderCommand $addOrderCommand
     * @param $scope
     * @throws ApiException
     * @throws GetresponseApiException
     */
    public function addOrder(AddOrderCommand $addOrderCommand, Scope $scope)
    {
        $orderService = $this->orderServiceFactory->create($scope);
        $orderService->addOrder($addOrderCommand);
    }

    /**
     * @param EditOrderCommand $editOrderCommand
     * @param Scope $scope
     * @throws ApiException
     * @throws GetresponseApiException
     */
    public function updateOrder(EditOrderCommand $editOrderCommand, Scope $scope)
    {
        $orderService = $this->orderServiceFactory->create($scope);
        $orderService->updateOrder($editOrderCommand);
    }
}
