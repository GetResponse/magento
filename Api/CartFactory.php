<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

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
            $this->customerFactory->create((int) $quote->getCustomerId()),
            $this->createLinesFromQuote($quote),
            (float)$quote->getGrandTotal(),
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
            $children = $item->getChildren();

            if (!empty($children)) {
                /** @var Item $child */
                foreach ($children as $child) {
                    $lines[] = new Line(
                        (int)$child->getProduct()->getId(),
                        (float)$child->getPrice(),
                        (float)$child->getPriceInclTax(),
                        (int)$child->getQty(),
                        (string) $child->getSku()
                    );
                }
            } else {
                $lines[] = new Line(
                    (int)$item->getProduct()->getId(),
                    (float)$item->getPrice(),
                    (float)$item->getPriceInclTax(),
                    (int)$item->getQty(),
                    (string) $item->getSku()
                );
            }
        }

        return $lines;
    }
}
