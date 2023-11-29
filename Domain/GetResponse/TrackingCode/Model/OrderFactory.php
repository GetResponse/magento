<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model;

use Magento\Catalog\Model\CategoryRepository;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order as MagentoOrder;

class OrderFactory
{
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function create(MagentoOrder $magentoOrder): Order
    {
        return new Order(
            (int) $magentoOrder->getId(),
            (int) $magentoOrder->getQuoteId(),
            (float) $magentoOrder->getGrandTotal(),
            $magentoOrder->getOrderCurrencyCode(),
            $this->createProducts($magentoOrder)
        );
    }

    private function createProducts(MagentoOrder $magentoOrder): array
    {
        $products = [];

        foreach ($magentoOrder->getAllVisibleItems() as $item) {
            $products[] = new Product(
                (int)$item->getProductId(),
                $item->getProduct()->getName(),
                (float)$item->getPriceInclTax(),
                $item->getSku(),
                $magentoOrder->getOrderCurrencyCode(),
                (int)$item->getQtyOrdered(),
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
