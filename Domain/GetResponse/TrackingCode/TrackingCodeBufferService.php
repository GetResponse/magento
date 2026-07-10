<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode;

use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model\Cart;
use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model\Order;
use Magento\Customer\Model\Session;

class TrackingCodeBufferService
{
    // phpcs:ignore
    private $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Handle add cart to buffer.
     *
     * @param Cart $cart
     */
    public function addCartToBuffer(Cart $cart): void
    {
        $this->session->setGrBufferedCart($cart->toArray());
    }

    /**
     * Get cart from buffer.
     */
    public function getCartFromBuffer(): array
    {
        $cart = $this->session->getGrBufferedCart();

        if (is_array($cart) && count($cart) > 0) {
            $this->session->unsGrBufferedCart();
            return $cart;
        }

        return [];
    }

    /**
     * Handle add order to buffer.
     *
     * @param Order $order
     */
    public function addOrderToBuffer(Order $order): void
    {
        $this->session->setGrBufferedOrder($order->toArray());
    }

    /**
     * Get order from buffer.
     */
    public function getOrderFromBuffer(): array
    {
        $order = $this->session->getGrBufferedOrder();

        if (is_array($order) && count($order) > 0) {
            $this->session->unsGrBufferedOrder();
            return $order;
        }

        return [];
    }

    /**
     * Check user logged in.
     */
    public function isUserLoggedIn(): bool
    {
        return $this->session->isLoggedIn();
    }

    /**
     * Handle pull product id added to wish list.
     */
    public function pullProductIdAddedToWishList(): ?string
    {
        $productIdAddedToWishList = $this->session->getGrProductAddedToWishList();

        if ($productIdAddedToWishList) {
            $this->session->unsGrProductAddedToWishList();
        }

        return $productIdAddedToWishList;
    }

    /**
     * Set product id added to wish list.
     *
     * @param string $productId
     */
    public function setProductIdAddedToWishList(string $productId): void
    {
        $this->session->setGrProductAddedToWishList($productId);
    }

    /**
     * Handle pull product id removed from wish list.
     */
    public function pullProductIdRemovedFromWishList(): ?string
    {
        $productIdAddedToWishList = $this->session->getGrProductRemovedFromWishList();

        if ($productIdAddedToWishList) {
            $this->session->unsGrProductRemovedFromWishList();
        }

        return $productIdAddedToWishList;
    }

    /**
     * Set product id removed from wish list.
     *
     * @param string $productId
     */
    public function setProductIdRemovedFromWishList(string $productId): void
    {
        $this->session->setGrProductRemovedFromWishList($productId);
    }
}
