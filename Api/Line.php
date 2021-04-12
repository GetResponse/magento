<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Line implements JsonSerializable
{
    private $variantId;
    private $price;
    private $priceTax;
    private $quantity;
    private $sku;

    public function __construct(
        int $variantId,
        float $price,
        float $priceTax,
        int $quantity,
        string $sku
    ) {
        $this->variantId = $variantId;
        $this->price = $price;
        $this->priceTax = $priceTax;
        $this->quantity = $quantity;
        $this->sku = $sku;
    }

    public function getVariantId(): int
    {
        return $this->variantId;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getPriceTax(): float
    {
        return $this->priceTax;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function jsonSerialize(): array
    {
        return [
            'variant_id' => $this->variantId,
            'price' => $this->price,
            'price_tax' => $this->priceTax,
            'quantity' => $this->quantity,
            'sku' => $this->sku
        ];
    }
}
