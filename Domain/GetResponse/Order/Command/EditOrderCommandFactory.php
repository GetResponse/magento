<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Command;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Exception\InvalidOrderException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderFactory;
use GrShareCode\Order\Command\EditOrderCommand;
use Magento\Sales\Model\Order;

class EditOrderCommandFactory
{
    private $orderFactory;

    public function __construct(OrderFactory $orderFactory)
    {
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param Order $order
     * @param $shopId
     * @return EditOrderCommand
     * @throws InvalidOrderException
     */
    public function createForOrderService(Order $order, $shopId): EditOrderCommand
    {
        return new EditOrderCommand(
            $this->orderFactory->fromMagentoOrder($order),
            $shopId
        );
    }
}
