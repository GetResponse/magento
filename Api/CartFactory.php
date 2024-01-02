<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Checkout\Helper\Cart as CartHelper;
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

    public function create(Quote $quote): Cart
    {
        return new Cart(
            (int)$quote->getId(),
            $this->customerFactory->create($quote->getCustomer()),
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
