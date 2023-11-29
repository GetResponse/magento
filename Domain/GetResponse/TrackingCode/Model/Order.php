<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model;

class Order
{
    private $id;
    private $cartId;
    private $price;
    private $currency;
    /** @var array<Product> */
    private $products;

    public function __construct(int $id, int $cartId, float $price, string $currency, array $products)
    {
        $this->id = $id;
        $this->cartId = $cartId;
        $this->price = $price;
        $this->currency = $currency;
        $this->products = $products;
    }

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
            'price' => $this->price,
            'cartId' => (string)$this->cartId,
            'orderId' => (string)$this->id,
            'currency' => $this->currency,
            'products' => $products
        ];
    }
}
