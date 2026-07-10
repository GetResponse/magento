<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class ProductSalePrice implements JsonSerializable
{
    /** @var float */
    private $salePrice;
    /** @var ?string */
    private $saleStartsAt;
    /** @var ?string */
    private $saleEndsAt;

    /**
     * @param float $salePrice
     * @param ?string $saleStartsAt
     * @param ?string $saleEndsAt
     */
    public function __construct(float $salePrice, ?string $saleStartsAt, ?string $saleEndsAt)
    {
        $this->salePrice = $salePrice;
        $this->saleStartsAt = $saleStartsAt;
        $this->saleEndsAt = $saleEndsAt;
    }

    /**
     * Serialize object to JSON data.
     */
    public function jsonSerialize(): array
    {
        return [
            'sale_price' => $this->salePrice,
            'sale_starts_at' => $this->saleStartsAt,
            'sale_ends_at' => $this->saleEndsAt
        ];
    }
}
