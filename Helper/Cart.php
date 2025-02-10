<?php

namespace GetResponse\GetResponseIntegration\Helper;

use GetResponse\GetResponseIntegration\Application\GetResponse\Cart\CartIdEncryptor;
use Magento\Checkout\Helper\Cart as MagentoCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Checkout\Model\Cart as CartModel;
use GetResponse\GetResponseIntegration\Router\Router;

class Cart extends MagentoCart
{
    /**
     * @var CartIdEncryptor $cartIdEncryptor
     */
    private $cartIdEncryptor;

    public function __construct(
        Context         $context,
        CartModel       $checkoutCart,
        Session         $checkoutSession,
        CartIdEncryptor $cartIdEncryptor
    )
    {
        parent::__construct($context, $checkoutCart, $checkoutSession);
        $this->cartIdEncryptor = $cartIdEncryptor;
    }

    public function getCartUrl()
    {
        $cartId = (string)$this->getQuote()->getId();
        return $this->_getUrl(Router::ABANDON_CART_ROUTE, [
            'cartId' => $this->cartIdEncryptor->encrypt($cartId),
        ]);
    }
}
