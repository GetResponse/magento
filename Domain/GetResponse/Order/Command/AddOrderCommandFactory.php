<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Command;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Exception\InvalidOrderException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderFactory;
use GrShareCode\Order\Command\AddOrderCommand;
use Magento\Sales\Model\Order;

/**
 * Class AddOrderServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Order
 */
class AddOrderCommandFactory
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
     * @param string $contactListId
     * @param string $shopId
     * @return AddOrderCommand
     * @throws InvalidOrderException
     */
    public function createForMagentoOrder(Order $order, $contactListId, $shopId)
    {
        return new AddOrderCommand(
            $this->orderFactory->fromMagentoOrder($order),
            $order->getCustomerEmail(),
            $contactListId,
            $shopId
        );
    }
}
