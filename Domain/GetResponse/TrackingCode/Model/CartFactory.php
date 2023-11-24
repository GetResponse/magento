<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model;

use Magento\Catalog\Model\CategoryRepository;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

class CartFactory
{
    private $cartHelper;
    private $categoryRepository;

    public function __construct(CartHelper $cartHelper, CategoryRepository $categoryRepository)
    {
        $this->cartHelper = $cartHelper;
        $this->categoryRepository = $categoryRepository;
    }

    public function create(Quote $quote): Cart
    {
        return new Cart(
            (int) $quote->getId(),
            (float) $quote->getGrandTotal(),
            $quote->getQuoteCurrencyCode(),
            $this->cartHelper->getCartUrl(),
            $this->createProducts($quote)
        );
    }

    private function createProducts(Quote $quote): array
    {
        $products = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            $products[] = new Product(
                (int) $item->getProduct()->getId(),
                $item->getProduct()->getName(),
                $item->getConvertedPrice(),
                $item->getSku(),
                $quote->getQuoteCurrencyCode(),
                (int) $item->getTotalQty(),
                $this->getCategories($item)
            );
        }

        return $products;
    }

    private function getCategories(Item $item): array
    {
        $categories = [];

        foreach ($item->getProduct()->getCategoryIds() as $id) {
            $category = $this->categoryRepository->get($id);
            $categories[] = new Category((int) $category->getId(), $category->getName());
        }

        return $categories;
    }
}
