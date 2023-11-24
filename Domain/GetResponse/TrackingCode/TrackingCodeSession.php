<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode;

use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model\Cart;
use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model\Order;
use Magento\Customer\Model\Session;

class TrackingCodeSession
{
    /** @var Session */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function addCartToBuffer(Cart $cart): void
    {
        $this->session->setGrBufferedCart($cart->toArray());
    }

    public function getCartFromBuffer(): array
    {
        $cart = $this->session->getGrBufferedCart();

        if (is_array($cart) && count($cart) > 0) {
            $this->session->unsGrBufferedCart();
            return $cart;
        }

        return [];
    }

    public function addOrderToBuffer(Order $order): void
    {
        $this->session->setGrBufferedOrder($order->toArray());
    }

    public function getOrderFromBuffer(): array
    {
        $order = $this->session->getGrBufferedOrder();

        if (is_array($order) && count($order) > 0) {
            $this->session->unsGrBufferedOrder();
            return $order;
        }

        return [];
    }
}
