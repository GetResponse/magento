<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Command;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Exception\InvalidOrderException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderFactory;
use GrShareCode\Order\Command\EditOrderCommand;
use Magento\Sales\Model\Order;

/**
 * Class EditOrderCommandFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Command
 */
class EditOrderCommandFactory
{
    /** @var OrderFactory */
    private $orderFactory;

    /**
     * @param OrderFactory $orderFactory
     */
    public function __construct(OrderFactory $orderFactory)
    {
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param Order $order
     * @param string $shopId
     * @return EditOrderCommand
     * @throws InvalidOrderException
     */
    public function createForOrderService(Order $order, $shopId)
    {
        return new EditOrderCommand(
            $this->orderFactory->fromMagentoOrder($order),
            $shopId
        );
    }
}
