<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Cart\AddCartCommand;
use GrShareCode\Cart\Cart;
use GrShareCode\Cart\CartService as GrCartService;
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
    /** @var GrCartService */
    private $grCartService;

    /** @var Repository */
    private $repository;

    /** @var ProductFactory */
    private $productFactory;

    /**
     * @param CartServiceFactory $cartServiceFactory
     * @param Repository $repository
     * @param ProductFactory $productFactory
     * @throws ApiTypeException
     */
    public function __construct(
        CartServiceFactory $cartServiceFactory,
        Repository $repository,
        ProductFactory $productFactory
    ) {
        $this->grCartService = $cartServiceFactory->create();
        $this->repository = $repository;
        $this->productFactory = $productFactory;
    }

    /**
     * @param int $quoteId
     * @param string $contactListId
     * @param string $grShopId
     * @throws GetresponseApiException
     */
    public function exportCart($quoteId, $contactListId, $grShopId)
    {
        $quote = $this->repository->getQuoteById($quoteId);

        $cart = $this->getCart($quote);

        $this->grCartService->exportCart(
            new AddCartCommand($cart, $quote->getCustomerEmail(), $contactListId, $grShopId)
        );
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
     */
    public function sendCart($quoteId, $contactListId, $grShopId)
    {
        $quote = $this->repository->getQuoteById($quoteId);

        $cart = $this->getCart($quote);

        $this->grCartService->sendCart(
            new AddCartCommand($cart, $quote->getCustomerEmail(), $contactListId, $grShopId)
        );
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