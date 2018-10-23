<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\GetresponseApiException;
use GrShareCode\Order\AddOrderCommand;

/**
 * Class OrderService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Order
 */
class OrderService
{
    /** @var OrderServiceFactory */
    private $orderServiceFactory;

    /**
     * @param OrderServiceFactory $orderServiceFactory
     */
    public function __construct(OrderServiceFactory $orderServiceFactory) {
        $this->orderServiceFactory = $orderServiceFactory;
    }

    /**
     * @param AddOrderCommand $addOrderCommand
     * @throws ApiTypeException
     * @throws GetresponseApiException
     * @throws ConnectionSettingsException
     */
    public function exportOrder(AddOrderCommand $addOrderCommand)
    {
        $addOrderCommand->setToSkipAutomation();
        $orderService = $this->orderServiceFactory->create();
        $orderService->sendOrder($addOrderCommand);
    }

    /**
     * @param AddOrderCommand $addOrderCommand
     * @throws ApiTypeException
     * @throws ConnectionSettingsException
     * @throws GetresponseApiException
     */
    public function sendOrder(AddOrderCommand $addOrderCommand)
    {
        $orderService = $this->orderServiceFactory->create();
        $orderService->sendOrder($addOrderCommand);
    }

}