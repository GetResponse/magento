<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Quote\Model\Quote\Item;
use \Magento\Sales\Model\Order as MagentoOrder;

class OrderFactory
{
    private $customerFactory;
    private $addressFactory;

    public function __construct(
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory
    ) {
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
    }

    public function create(MagentoOrder $order): Order
    {
        $shippingAddress = null !== $order->getShippingAddress() ? $this->addressFactory->create($order->getShippingAddress()) : null;
        $billingAddress = null !== $order->getBillingAddress() ? $this->addressFactory->create($order->getBillingAddress()) : null;

        return new Order(
            (int)$order->getId(),
            (int)$order->getQuoteId(),
            $order->getCustomerEmail(),
            $this->customerFactory->create((int)$order->getCustomerId()),
            $this->createLinesFromOrder($order),
            null,
            (float)$order->getBaseSubtotal(),
            (float)$order->getGrandTotal(),
            (float)$order->getBaseShippingAmount(),
            $order->getOrderCurrencyCode(),
            $order->getStatus(),
            null,
            $shippingAddress,
            $billingAddress,
            $order->getCreatedAt(),
            $order->getUpdatedAt()
        );
    }

    private function createLinesFromOrder(MagentoOrder $order): array
    {
        $lines = [];

        foreach ($order->getAllVisibleItems() as $item) {
            $children = $item->getChildren();

            if (!empty($children)) {
                /** @var Item $child */
                foreach ($children as $child) {

                    $lines[] = new Line(
                        (int)$child->getProduct()->getId(),
                        (float)$child->getPrice(),
                        (float)$child->getPriceInclTax(),
                        (int)$child->getQtyOrdered()
                    );
                }
            } else {

                $lines[] = new Line(
                    (int)$item->getProduct()->getId(),
                    (float)$item->getPrice(),
                    (float)$item->getPriceInclTax(),
                    (int)$item->getQtyOrdered()
                );
            }
        }

        return $lines;
    }
}
