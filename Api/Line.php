<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Line implements JsonSerializable
{
    /** @var int */
    private $variantId;
    /** @var float */
    private $price;
    /** @var float */
    private $priceTax;
    /** @var int */
    private $quantity;
    /** @var string */
    private $sku;

    /**
     * @param int $variantId
     * @param float $price
     * @param float $priceTax
     * @param int $quantity
     * @param string $sku
     */
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

    /**
     * Get variant id.
     */
    public function getVariantId(): int
    {
        return $this->variantId;
    }

    /**
     * Get price.
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * Get price tax.
     */
    public function getPriceTax(): float
    {
        return $this->priceTax;
    }

    /**
     * Get quantity.
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Get sku.
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * Serialize object to JSON data.
     */
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
