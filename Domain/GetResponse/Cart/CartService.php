<?php

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Cart\AddCartCommand;
use GrShareCode\Cart\Cart;
use GrShareCode\GetresponseApiException;
use GrShareCode\Product\Product;
use GrShareCode\Product\ProductsCollection;
use GrShareCode\Product\Variant\Variant;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

/**
 * Class CartService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Cart
 */
class CartService
{
    /** @var CartServiceFactory */
    private $cartServiceFactory;

    /** @var Repository */
    private $repository;

    /** @var ProductFactory */
    private $productFactory;

    /**
     * @param CartServiceFactory $cartServiceFactory
     * @param Repository $repository
     * @param ProductFactory $productFactory
     */
    public function __construct(
        CartServiceFactory $cartServiceFactory,
        Repository $repository,
        ProductFactory $productFactory
    ) {
        $this->cartServiceFactory = $cartServiceFactory;
        $this->repository = $repository;
        $this->productFactory = $productFactory;
    }

    /**
     * @param Quote $quote
     * @return Cart
     */
    private function getCart(Quote $quote)
    {
        $productCollection = new ProductsCollection();

        /** @var Item $quoteItem */
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $productCollection->add(
                $this->productFactory->fromMagentoQuoteItem($quoteItem)
            );
        }

        return new Cart(
            $quote->getId(),
            $productCollection,
            $quote->getQuoteCurrencyCode(),
            (float)$quote->getGrandTotal(),
            $this->getQuotePriceInclTax($productCollection)
        );
    }

    /**
     * @param int $quoteId
     * @param string $contactListId
     * @param string $grShopId
     * @throws GetresponseApiException
     * @throws ApiTypeException
     */
    public function sendCart($quoteId, $contactListId, $grShopId)
    {
        $cartService = $this->cartServiceFactory->create();
        $quote = $this->repository->getQuoteById($quoteId);
        $cart = $this->getCart($quote);
        $cartService->sendCart(new AddCartCommand(
            $cart, $quote->getCustomerEmail(), $contactListId, $grShopId
        ));
    }

    /**
     * @param ProductsCollection $productsCollection
     * @return float
     */
    private function getQuotePriceInclTax(ProductsCollection $productsCollection)
    {
        $priceInclTax = 0.00;
        /** @var Product $product */
        foreach ($productsCollection as $product) {
            /** @var Variant $variant */
            foreach ($product->getVariants() as $variant) {
                $priceInclTax += $variant->getPriceTax() * $variant->getQuantity();
            }
        }
        return (float)$priceInclTax;
    }

}