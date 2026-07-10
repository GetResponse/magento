<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model;

class Cart
{
    /** @var int */
    private $id;
    /** @var float */
    private $price;
    /** @var string */
    private $currency;
    /** @var string */
    private $url;
    /** @var array<Product> */
    private $products;

    /**
     * @param int $id
     * @param float $price
     * @param string $currency
     * @param string $url
     * @param array $products
     */
    public function __construct(int $id, float $price, string $currency, string $url, array $products)
    {
        $this->id = $id;
        $this->price = $price;
        $this->currency = $currency;
        $this->url = $url;
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
            'price' => $this->price,
            'cartId' => (string)$this->id,
            'currency' => $this->currency,
            'cartUrl' => $this->url,
            'products' => $products
        ];
    }
}
