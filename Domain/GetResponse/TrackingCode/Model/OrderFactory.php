<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model;

use Magento\Catalog\Model\CategoryRepository;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

class OrderFactory
{
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function create(Quote $quote): Order
    {
        return new Order(
            (int) $quote->getId(),
            (float) $quote->getGrandTotal(),
            $quote->getQuoteCurrencyCode(),
            '',
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
