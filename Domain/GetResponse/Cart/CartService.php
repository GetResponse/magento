<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Quote\ReadModel\Query\QuoteById;
use GetResponse\GetResponseIntegration\Domain\Magento\Quote\ReadModel\QuoteReadModel;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Cart\Cart;
use GrShareCode\Cart\Command\AddCartCommand;
use GrShareCode\Product\Product;
use GrShareCode\Product\ProductsCollection;
use GrShareCode\Product\Variant\Variant;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Quote\Model\Quote;

class CartService
{
    private $cartServiceFactory;
    private $productFactory;
    private $cartHelper;
    private $quoteReadModel;

    public function __construct(
        CartServiceFactory $cartServiceFactory,
        ProductFactory $productFactory,
        CartHelper $cartHelper,
        QuoteReadModel $quoteReadModel
    ) {
        $this->cartServiceFactory = $cartServiceFactory;
        $this->productFactory = $productFactory;
        $this->cartHelper = $cartHelper;
        $this->quoteReadModel = $quoteReadModel;
    }

    /**
     * @throws ApiException
     * @throws GetresponseApiException
     */
    public function sendCart($quoteId, $contactListId, $grShopId, Scope $scope): void
    {
        $cartService = $this->cartServiceFactory->create($scope);
        $quote = $this->quoteReadModel->getQuoteById(new QuoteById($quoteId));
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

    private function getCart(Quote $quote): Cart
    {
        $productCollection = new ProductsCollection();

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

    private function getQuotePriceInclTax(ProductsCollection $productsCollection): float
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
