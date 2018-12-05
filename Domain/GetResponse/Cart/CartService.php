<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Cart\Cart;
use GrShareCode\Cart\Command\AddCartCommand;
use GrShareCode\Product\Product;
use GrShareCode\Product\ProductsCollection;
use GrShareCode\Product\Variant\Variant;
use Magento\Checkout\Helper\Cart as CartHelper;
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

    /** @var CartHelper */
    private $cartHelper;

    /**
     * @param CartServiceFactory $cartServiceFactory
     * @param Repository $repository
     * @param ProductFactory $productFactory
     * @param CartHelper $cartHelper
     */
    public function __construct(
        CartServiceFactory $cartServiceFactory,
        Repository $repository,
        ProductFactory $productFactory,
        CartHelper $cartHelper
    ) {
        $this->cartServiceFactory = $cartServiceFactory;
        $this->repository = $repository;
        $this->productFactory = $productFactory;
        $this->cartHelper = $cartHelper;
    }

    /**
     * @param int $quoteId
     * @param string $contactListId
     * @param string $grShopId
     * @throws GetresponseApiException
     * @throws ApiException
     */
    public function sendCart($quoteId, $contactListId, $grShopId)
    {
        $cartService = $this->cartServiceFactory->create();
        $quote = $this->repository->getQuoteById($quoteId);
        $cart = $this->getCart($quote);
        $cartService->sendCart(
            new AddCartCommand(
                $cart,
                $quote->getCustomerEmail(),
                $contactListId,
                $grShopId
            )
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
            $this->getQuotePriceInclTax($productCollection),
            $this->cartHelper->getCartUrl()
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