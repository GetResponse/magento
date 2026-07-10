<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Variant implements JsonSerializable
{
    /** @var int */
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $sku;
    /** @var float */
    private $price;
    /** @var float */
    private $priceTax;
    /** @var ?float */
    private $previousPrice;
    /** @var ?float */
    private $previousPriceTax;
    /** @var int */
    private $quantity;
    /** @var string */
    private $url;
    /** @var ?int */
    private $position;
    /** @var ?int */
    private $barcode;
    /** @var string */
    private $description;
    /** @var string */
    private $shortDescription;
    /** @var null|Image[] */
    private $images;
    /** @var string */
    private $status;
    /** @var ?ProductSalePrice */
    private $salePrice;

    /**
     * @param int $id
     * @param string $name
     * @param string $sku
     * @param float $price
     * @param float $priceTax
     * @param ?float $previousPrice
     * @param ?float $previousPriceTax
     * @param int $quantity
     * @param string $url
     * @param ?int $position
     * @param ?int $barcode
     * @param string $description
     * @param string $shortDescription
     * @param ?array $images
     * @param string $status
     * @param ?ProductSalePrice $salePrice
     */
    public function __construct(
        int $id,
        string $name,
        string $sku,
        float $price,
        float $priceTax,
        ?float $previousPrice,
        ?float $previousPriceTax,
        int $quantity,
        string $url,
        ?int $position,
        ?int $barcode,
        string $description,
        string $shortDescription,
        ?array $images,
        string $status,
        ?ProductSalePrice $salePrice = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->sku = $sku;
        $this->price = $price;
        $this->priceTax = $priceTax;
        $this->previousPrice = $previousPrice;
        $this->previousPriceTax = $previousPriceTax;
        $this->quantity = $quantity;
        $this->url = $url;
        $this->position = $position;
        $this->barcode = $barcode;
        $this->description = $description;
        $this->shortDescription = $shortDescription;
        $this->images = $images;
        $this->status = $status;
        $this->salePrice = $salePrice;
    }

    /**
     * Serialize object to JSON data.
     */
    public function jsonSerialize(): array
    {
        $images = [];
        foreach ($this->images as $image) {
            $images[] = $image->jsonSerialize();
        }

        $payload = [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'price_tax' => $this->priceTax,
            'previous_price' => $this->previousPrice,
            'previous_price_tax' => $this->previousPriceTax,
            'quantity' => $this->quantity,
            'url' => $this->url,
            'position' => $this->position,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'short_description' => $this->shortDescription,
            'images' => $images,
            'status' => $this->status
        ];

        if (null !== $this->salePrice) {
            $payload = array_merge($payload, $this->salePrice->jsonSerialize());
        }

        return $payload;
    }
}
