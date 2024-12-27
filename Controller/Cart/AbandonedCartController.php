<?php

namespace Controller\Cart;

class AbandonedCartController extends \Magento\Framework\App\Action\Action implements \Controller\Cart\AbandonedCartControllerInterface
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context           $context,
        \Magento\Quote\Api\CartRepositoryInterface      $cartRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Helper\Cart                   $cartHelper
    )
    {
        parent()::__construct($context);
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->cartHelper = $cartHelper;
    }


    public function execute(): never
    {
        $params = $this->getRequest()->getParams();
        $hasCartId = isset($params['cartId']);

        if ($hasCartId) {
            $this->executeWithCartId($params['cartId']);
            exit;
        }

        if (isset($params['products'])) {
            $this->executeWithProducts($params['products']);
            exit;
        }

        throw new \Magento\Framework\Exception\ValidatorException("Either `cartId` or `products` parameter is required");
    }

    private function executeWithCartId(int $cartId): never
    {
        $quote = $this->cartRepository->get($cartId);

        if (!$quote) {
            throw new \Magento\Framework\Exception\NotFoundException("Cart not found");
        }

        $quote->setReservedOrderId(null);
        $quote->setIsActive(true);
        $quote->removePayment();

        $cart = $this->cartHelper->getCart();
        $cart->truncate();
        $cart->setQuote($quote);
        $cart->save();

        $this->_redirect($this->cartHelper->getCartUrl());
    }

    private function executeWithProducts(string $products): never
    {
        $productsPayloadUnserializer = new ProductsPayloadUnserializer();
        $products = $productsPayloadUnserializer->fromAbandonedCart($products);

        $cart = $this->cartHelper->getCart();
        $cart->truncate();

        foreach ($products as $product) {
            $this->cartHelper->addProduct($this->productRepository->getById($product['id']), $product['qty']);
        }

        $this->_redirect($this->cartHelper->getCartUrl());
    }
}
