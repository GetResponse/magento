<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model;

use Magento\Catalog\Model\CategoryRepository;
use Magento\Sales\Model\Order as MagentoOrder;
use Magento\Sales\Model\Order\Item;

class OrderFactory
{
    /** @var CategoryRepository */
    private $categoryRepository;

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Handle create.
     *
     * @param MagentoOrder $magentoOrder
     */
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

    /**
     * Create products.
     *
     * @param MagentoOrder $magentoOrder
     */
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

    /**
     * Get categories.
     *
     * @param Item $item
     */
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
