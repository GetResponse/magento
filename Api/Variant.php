<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

class Variant
{
    private $id;
    private $name;
    private $sku;
    private $price;
    private $priceTax;
    private $previousPrice;
    private $previousPriceTax;
    private $quantity;
    private $position;
    private $barcode;
    private $description;
    private $images;

    public function __construct(
        int $id,
        string $name,
        string $sku,
        float $price,
        float $priceTax,
        float $previousPrice,
        float $previousPriceTax,
        int $quantity,
        int $position,
        int $barcode,
        string $description,
        array $images
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->sku = $sku;
        $this->price = $price;
        $this->priceTax = $priceTax;
        $this->previousPrice = $previousPrice;
        $this->previousPriceTax = $previousPriceTax;
        $this->quantity = $quantity;
        $this->position = $position;
        $this->barcode = $barcode;
        $this->description = $description;
        $this->images = $images;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getPriceTax(): float
    {
        return $this->priceTax;
    }

    public function getPreviousPrice(): float
    {
        return $this->previousPrice;
    }

    public function getPreviousPriceTax(): float
    {
        return $this->previousPriceTax;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getBarcode(): int
    {
        return $this->barcode;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getImages(): array
    {
        return $this->images;
    }
}
