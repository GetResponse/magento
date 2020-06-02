<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Order\ReadModel;

use GetResponse\GetResponseIntegration\Domain\Magento\Order\ReadModel\Query\CustomerOrders;
use GetResponse\GetResponseIntegration\Domain\Magento\Order\ReadModel\Query\GetOrder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;

class OrderReadModel
{
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getOrder(GetOrder $query): Order
    {
        $order_object = $this->objectManager->create(Order::class);
        return $order_object->load($query->getId());
    }

    public function getCustomerOrders(CustomerOrders $query): Collection
    {
        return $this->objectManager->create(Order::class)
            ->getCollection()
            ->addFieldToFilter('customer_id', $query->getId())
            ->setOrder('created_at', 'desc');
    }
}
