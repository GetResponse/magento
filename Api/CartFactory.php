<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\Visitor;
use GetResponse\GetResponseIntegration\Helper\Cart as CartHelper;
use Magento\Quote\Model\Quote;

class CartFactory
{
    private $cartHelper;
    private $customerFactory;

    public function __construct(CartHelper $cartHelper, CustomerFactory $customerFactory)
    {
        $this->cartHelper = $cartHelper;
        $this->customerFactory = $customerFactory;
    }

    public function create(Quote $quote, ?Visitor $visitor): Cart
    {
        return new Cart(
            (int)$quote->getId(),
            $quote->getCustomerIsGuest() ? null : $this->customerFactory->createFromQuote($quote),
            $visitor,
            $this->createLinesFromQuote($quote),
            (float)$quote->getSubtotal(),
            (float)$quote->getGrandTotal(),
            $quote->getQuoteCurrencyCode(),
            $this->cartHelper->getCartUrl(),
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
                (int) $variantId,
                (float)$item->getConvertedPrice(),
                (float)$item->getPriceInclTax(),
                (int)$item->getTotalQty(),
                (string) $item->getSku()
            );
        }

        return $lines;
    }
}
