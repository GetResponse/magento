<?php

namespace GetResponse\GetResponseIntegration\Helper;

class Cart extends \Magento\Checkout\Helper\Cart
{
    public function __construct(
        \Magento\Framework\App\Helper\Context                                            $context,
        \Magento\Checkout\Model\Cart                                                     $checkoutCart,
        \Magento\Checkout\Model\Session                                                  $checkoutSession,
        \GetResponse\GetResponseIntegration\Application\GetResponse\Cart\CartIdEncryptor $cartIdEncryptor
    )
    {
        parent::__construct($context, $checkoutCart, $checkoutSession);
    }

    public function getCartUrl()
    {
        $cartId = $this->getQuote()->getId();
        return $this->_getUrl('abandonCart', [
            'cartId' => $this->cartIdEncryptor->encrypt($cartId),
        ]);
    }
}
