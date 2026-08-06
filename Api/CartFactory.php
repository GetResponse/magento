<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\Visitor;
use GetResponse\GetResponseIntegration\Helper\Cart as CartHelper;
use Magento\Quote\Model\Quote;

class CartFactory
{
    /** @var CartHelper */
    private $cart;
    /** @var CustomerFactory */
    private $customerFactory;

    /**
     * @param CartHelper $cart
     * @param CustomerFactory $customerFactory
     */
    public function __construct(CartHelper $cart, CustomerFactory $customerFactory)
    {
        $this->cart = $cart;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Handle create.
     *
     * @param Quote $quote
     * @param ?Visitor $visitor
     */
    public function create(Quote $quote, ?Visitor $visitor = null): Cart
    {
        $totalPriceInclTaxAfterDiscount = $this->calculateTotalPriceInclTaxAfterDiscount($quote);

        return new Cart(
            (int)$quote->getId(),
            (bool) $quote->getCustomerIsGuest() ? null : $this->customerFactory->create($quote->getCustomer()),
            $visitor,
            $this->createLinesFromQuote($quote),
            $totalPriceInclTaxAfterDiscount,
            $totalPriceInclTaxAfterDiscount,
            $quote->getQuoteCurrencyCode(),
            $this->cart->getCartUrl(),
            $quote->getCreatedAt(),
            $quote->getUpdatedAt()
        );
    }

    /**
     * Calculate total price.
     *
     * @param Quote $quote
     * @return float
     */
    private function calculateTotalPriceInclTaxAfterDiscount(Quote $quote): float
    {
        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();

        if ($address === null) {
            $address = $quote->getBillingAddress();
        }

        if ($address === null) {
            return (float) $quote->getGrandTotal();
        }

        return (float) $address->getSubtotalInclTax() + (float) $address->getDiscountAmount();
    }

    /**
     * Create lines from quote.
     *
     * @param Quote $quote
     */
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

            $quantity = (float)$item->getTotalQty();
            $itemPriceInclTaxAfterDiscount = (float)$item->getPriceInclTax();
            if ($quantity > 0) {
                $itemPriceInclTaxAfterDiscount -= (float)$item->getDiscountAmount() / $quantity;
            }

            $lines[] = new Line(
                (int)$variantId,
                $itemPriceInclTaxAfterDiscount,
                $itemPriceInclTaxAfterDiscount,
                (int)$item->getTotalQty(),
                (string)$item->getSku()
            );
        }

        return $lines;
    }
}
