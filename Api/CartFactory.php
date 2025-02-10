<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\Visitor;
use GetResponse\GetResponseIntegration\Helper\Cart as CartHelper;
use Magento\Checkout\Helper\Cart as MagentoCart;
use Magento\Quote\Model\Quote;
use GetResponse\GetResponseIntegration\Api\Line;

class CartFactory
{
    private $cart;
    private $customerFactory;

    public function __construct(CartHelper $cart, CustomerFactory $customerFactory)
    {
        $this->cart = $cart;
        $this->customerFactory = $customerFactory;
    }

    public function create(Quote $quote, ?Visitor $visitor = null): Cart
    {
        return new Cart(
            (int)$quote->getId(),
            (bool) $quote->getCustomerIsGuest() ? null : $this->customerFactory->create($quote->getCustomer()),
            $visitor,
            $this->createLinesFromQuote($quote),
            (float)$quote->getSubtotal(),
            (float)$quote->getGrandTotal(),
            $quote->getQuoteCurrencyCode(),
            $this->cart->getCartUrl(),
            $quote->getCreatedAt(),
            $quote->getUpdatedAt()
        );
    }

    private function createLinesFromQuote(Quote $quote): array
    {
        $lines = [];

        foreach ($quote->getAllVisibleItems() as $item) {

            $quantityOptions = $item->getQtyOptions();

            if (count($quantityOptions) > 0) {
                $quantityOption = reset($quantityOptions);
                $variantId = $quantityOption->getProduct()->getId();
            } else {
                $variantId = $item->getProduct()->getId();
            }

            $lines[] = new Line(
                (int)$variantId,
                (float)$item->getConvertedPrice(),
                (float)$item->getPriceInclTax(),
                (int)$item->getTotalQty(),
                (string)$item->getSku()
            );
        }

        return $lines;
    }
}
