<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class ProductSalePrice implements JsonSerializable
{
    private $salePrice;
    private $saleStartsAt;
    private $saleEndsAt;

    public function __construct(float $salePrice, ?string $saleStartsAt, ?string $saleEndsAt)
    {
        $this->salePrice = $salePrice;
        $this->saleStartsAt = $saleStartsAt;
        $this->saleEndsAt = $saleEndsAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'sale_price' => $this->salePrice,
            'sale_starts_at' => $this->saleStartsAt,
            'sale_ends_at' => $this->saleEndsAt
        ];
    }
}
