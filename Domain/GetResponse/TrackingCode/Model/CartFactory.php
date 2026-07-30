<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model;

use GetResponse\GetResponseIntegration\Helper\Cart as MagentoCart;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

class CartFactory
{
    /** @var MagentoCart */
    private $cart;
    /** @var CategoryRepository */
    private $categoryRepository;

    /**
     * @param MagentoCart $cart
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(MagentoCart $cart, CategoryRepository $categoryRepository)
    {
        $this->cart = $cart;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Handle create.
     *
     * @param Quote $quote
     * @param string|null $shopId
     */
    public function create(Quote $quote, ?string $shopId): Cart
    {
        return new Cart(
            $shopId,
            (int) $quote->getId(),
            (float) $quote->getGrandTotal(),
            $quote->getQuoteCurrencyCode(),
            $this->cart->getCartUrl(),
            $this->createProducts($quote)
        );
    }

    /**
     * Create products.
     *
     * @param Quote $quote
     */
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
