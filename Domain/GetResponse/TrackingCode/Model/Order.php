<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model;

class Order
{
    /** @var string|null */
    private $shopId;
    /** @var int */
    private $id;
    /** @var int */
    private $cartId;
    /** @var float */
    private $price;
    /** @var string */
    private $currency;
    /** @var array<Product> */
    private $products;

    /**
     * @param string|null $shopId
     * @param int $id
     * @param int $cartId
     * @param float $price
     * @param string $currency
     * @param array $products
     */
    public function __construct(?string $shopId, int $id, int $cartId, float $price, string $currency, array $products)
    {
        $this->shopId = $shopId;
        $this->id = $id;
        $this->cartId = $cartId;
        $this->price = $price;
        $this->currency = $currency;
        $this->products = $products;
    }

    /**
     * Handle to array.
     */
    public function toArray(): array
    {
        $products = [];

        foreach ($this->products as $product) {
            $categories = [];

            foreach ($product->getCategories() as $category) {
                $categories[] = $category->toArray();
            }

            $products[] = [
                'product' => $product->toArray(),
                'quantity' => $product->getQuantity(),
                'categories' => $categories,
            ];
        }

        return [
            'shop' => ['id' => $this->shopId],
            'price' => $this->price,
            'cartId' => (string)$this->cartId,
            'orderId' => (string)$this->id,
            'currency' => $this->currency,
            'products' => $products
        ];
    }
}
