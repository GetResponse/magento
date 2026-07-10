<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model;

class Product
{
    /** @var int */
    private $id;
    /** @var string */
    private $name;
    /** @var float */
    private $price;
    /** @var string */
    private $sku;
    /** @var string */
    private $currency;
    /** @var int */
    private $quantity;
    /** @var array<Category> */
    private $categories;

    /**
     * @param int $id
     * @param string $name
     * @param float $price
     * @param string $sku
     * @param string $currency
     * @param int $quantity
     * @param array $categories
     */
    public function __construct(
        int $id,
        string $name,
        float $price,
        string $sku,
        string $currency,
        int $quantity,
        array $categories
    ) {
        $this->id         = $id;
        $this->name       = $name;
        $this->price      = $price;
        $this->sku        = $sku;
        $this->currency   = $currency;
        $this->quantity   = $quantity;
        $this->categories = $categories;
    }

    /**
     * Get categories.
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Get quantity.
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Handle to array.
     */
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
