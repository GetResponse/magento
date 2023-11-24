<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model;

class Product
{
    private $id;
    private $name;
    private $price;
    private $sku;
    private $currency;
    private $quantity;
    /** @var array<Category> */
    private $categories;

    public function __construct(int $id, string $name, float $price, string $sku, string $currency, int $quantity, array $categories)
    {
        $this->id         = $id;
        $this->name       = $name;
        $this->price      = $price;
        $this->sku        = $sku;
        $this->currency   = $currency;
        $this->quantity   = $quantity;
        $this->categories = $categories;
    }

    public function getCategories(): array {
        return $this->categories;
    }

    public function getQuantity(): int {
        return $this->quantity;
    }

    public function toArray(): array
    {
        return [
            'id'       => (string) $this->id,
            'name'     => $this->name,
            'price'    => (string) $this->price,
            'sku'      => $this->sku,
            'currency' => $this->currency,
        ];
    }
}
