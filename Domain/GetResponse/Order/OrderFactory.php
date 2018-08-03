<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order;

use DateTime;
use GrShareCode\Address\Address;
use GrShareCode\Order\Order as GrOrder;
use GrShareCode\Product\ProductsCollection;
use Magento\Sales\Model\Order;

/**
 * Class OrderFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Order
 */
class OrderFactory
{
    /**
     * @param Order $order
     * @param ProductsCollection $productsCollection
     * @param mixed $shippingAddress
     * @param Address $billingAddress
     * @return GrOrder
     */
    public static function fromMagentoOrder(
        Order $order,
        ProductsCollection $productsCollection,
        $shippingAddress,
        $billingAddress
    ) {
        return new GrOrder(
            $order->getId(),
            $productsCollection,
            (float)$order->getBaseSubtotal(),
            (float)$order->getTaxAmount(),
            null,
            $order->getOrderCurrencyCode(),
            $order->getStatus(),
            $order->getQuoteId(),
            null,
            (float)$order->getShippingAmount(),
            null,
            DateTime::createFromFormat('Y-m-d H:i:s', $order->getCreatedAt())->format(DateTime::ISO8601),
            $shippingAddress,
            $billingAddress
        );
    }

}