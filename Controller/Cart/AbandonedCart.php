<?php

namespace GetResponse\GetResponseIntegration\Controller\Cart;

use Magento\Framework\Url;

class AbandonedCart extends \Magento\Framework\App\Action\Action
    implements \GetResponse\GetResponseIntegration\Controller\Cart\AbandonedCartInterface,
    \Magento\Framework\App\Action\HttpGetActionInterface
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

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context           $context,
        \Magento\Quote\Api\CartRepositoryInterface      $cartRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Helper\Cart                   $cartHelper,
        \Magento\Framework\Message\ManagerInterface     $messageManager,
        \Magento\Framework\UrlInterface                 $url,
        \Magento\Checkout\Model\Session                 $checkoutSession,
        \Magento\Quote\Model\QuoteFactory               $quoteFactory
    )
    {
        parent::__construct($context);
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->cartHelper = $cartHelper;
        $this->messageManager = $messageManager;
        $this->url = $url;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
    }


    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (isset($params['cartId'])) {
            return $this->executeWithCartId((int)$params['cartId']);
        }

        if (isset($params['products'])) {
            return $this->executeWithProducts((string)$params['products']);
        }

        return $this->_redirect($this->url->getUrl('noroute'));
    }


    private function executeWithCartId(int $cartId)
    {
        $quote = $this->quoteFactory->create()->loadByIdWithoutStore($cartId);

        if (empty($quote->getItems())) {
            $this->messageManager->addErrorMessage(__("Cannot recover an empty or inactive cart"));
            return $this->_redirect($this->cartHelper->getCartUrl());
        }

        $quote->setReservedOrderId(null);
        $quote->setIsActive(true);
        $quote->removePayment();
        $quote->save();

        $this->checkoutSession->clearStorage();
        $this->checkoutSession->replaceQuote($quote);

        return $this->_redirect($this->cartHelper->getCartUrl());
    }

    private function executeWithProducts(string $products)
    {
        $products = \GetResponse\GetResponseIntegration\Helper\ProductsPayloadDeserializer::fromAbandonedCart($products);

        if (empty($products)) {
            $this->messageManager->addErrorMessage(__("Products payload is empty"));
            return $this->_redirect($this->url->getUrl('noroute'));
        }

        $cart = $this->cartHelper->getCart();
        $cart->truncate();

        foreach ($products as $product) {
            $productEntity = $this->productRepository->getById($product['product_id']);
            $productData = new \Magento\Framework\DataObject(['qty' => $product['qty']]);
            $cart->addProduct($productEntity, $productData);
        }

        return $this->_redirect($this->cartHelper->getCartUrl());
    }
}
