<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode;

use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model\Cart;
use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model\Order;
use Magento\Customer\Model\Session;

class TrackingCodeBufferService
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

    public function isUserLoggedIn(): bool
    {
        return $this->session->isLoggedIn();
    }

    public function pullProductIdAddedToWishList(): ?string
    {
        $productIdAddedToWishList = $this->session->getGrProductAddedToWishList();

        if ($productIdAddedToWishList) {
            $this->session->unsGrProductAddedToWishList();
        }

        return $productIdAddedToWishList;
    }

    public function setProductIdAddedToWishList(string $productId): void
    {
        $this->session->setGrProductAddedToWishList($productId);
    }

    public function pullProductIdRemovedFromWishList(): ?string
    {
        $productIdAddedToWishList = $this->session->getGrProductRemovedFromWishList();

        if ($productIdAddedToWishList) {
            $this->session->unsGrProductRemovedFromWishList();
        }

        return $productIdAddedToWishList;
    }

    public function setProductIdRemovedFromWishList(string $productId): void
    {
        $this->session->setGrProductRemovedFromWishList($productId);
    }
}
