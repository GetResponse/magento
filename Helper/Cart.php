<?php

namespace GetResponse\GetResponseIntegration\Helper;

use GetResponse\GetResponseIntegration\Application\GetResponse\Cart\CartIdEncryptor;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Checkout\Model\Cart as CartModel;

class Cart extends CartHelper
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
        $cartId = $this->getQuote()->getId();
        return $this->_getUrl('abandonCart', [
            'cartId' => $this->cartIdEncryptor->encrypt($cartId),
        ]);
    }
}
