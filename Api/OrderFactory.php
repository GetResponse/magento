<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order as MagentoOrder;

class OrderFactory
{
    private $customerFactory;
    private $addressFactory;

    public function __construct(CustomerFactory $customerFactory, AddressFactory $addressFactory)
    {
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
    }

    public function create(MagentoOrder $order): Order
    {
        $shippingAddress = null;
        if ($order->getShippingAddress()) {
            $shippingAddress = $this->addressFactory->createFromOrder($order->getShippingAddress());
        }

        $billingAddress = null;
        if ($order->getBillingAddress()) {
            $billingAddress = $this->addressFactory->createFromOrder($order->getBillingAddress());
        }

        return new Order(
            (int)$order->getId(),
            (string)$order->getIncrementId(),
            (int)$order->getQuoteId(),
            $order->getCustomerEmail(),
            $this->customerFactory->createFromOrder($order),
            $this->createLinesFromOrder($order),
            null,
            (float)$order->getSubtotal(),
            (float)$order->getGrandTotal(),
            (float)$order->getShippingAmount(),
            $order->getOrderCurrencyCode(),
            $order->getStatus(),
            $order->getStatus(),
            $shippingAddress,
            $billingAddress,
            $order->getCreatedAt(),
            $order->getUpdatedAt()
        );
    }

    private function createLinesFromOrder(MagentoOrder $order): array
    {
        $lines = [];

        /** @var Item $item */
        foreach ($order->getAllVisibleItems() as $item) {

            $childrenItems = (array) $item->getChildrenItems();

            if (count($childrenItems) > 0) {
                $childItem = reset($childrenItems);
                $variantId = $childItem->getProductId();
                /** @var Item $child */
            } else {
                $variantId = $item->getProduct()->getId();
            }

            $lines[] = new Line(
                (int)$variantId,
                (float)$item->getPrice(),
                (float)$item->getPriceInclTax(),
                (int)$item->getQtyOrdered(),
                (string)$item->getSku()
            );
        }

        return $lines;
    }
}
