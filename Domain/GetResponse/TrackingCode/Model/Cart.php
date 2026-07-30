<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model;

class Cart
{
    /** @var string|null */
    private $shopId;
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
     * @param string|null $shopId
     * @param int $id
     * @param float $price
     * @param string $currency
     * @param string $url
     * @param array $products
     */
    public function __construct(?string $shopId, int $id, float $price, string $currency, string $url, array $products)
    {
        $this->shopId = $shopId;
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
            'shop' => ['id' => $this->shopId],
            'price' => $this->price,
            'cartId' => (string)$this->id,
            'currency' => $this->currency,
            'cartUrl' => $this->url,
            'products' => $products
        ];
    }
}
