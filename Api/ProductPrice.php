<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class ProductPrice implements JsonSerializable
{
    /** @var int */
    private $productId;
    /** @var float */
    private $regularPrice;
    /** @var float */
    private $finalPrice;
    /** @var bool */
    private $isDiscounted;

    /**
     * @param int $productId
     * @param float $regularPrice
     * @param float $finalPrice
     * @param bool $isDiscounted
     */
    public function __construct(
        int $productId,
        float $regularPrice,
        float $finalPrice,
        bool $isDiscounted
    ) {
        $this->productId = $productId;
        $this->regularPrice = $regularPrice;
        $this->finalPrice = $finalPrice;
        $this->isDiscounted = $isDiscounted;
    }

    /**
     * Get product id.
     *
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * Get regular price.
     *
     * @return float
     */
    public function getRegularPrice(): float
    {
        return $this->regularPrice;
    }

    /**
     * Get final price.
     *
     * @return float
     */
    public function getFinalPrice(): float
    {
        return $this->finalPrice;
    }

    /**
     * Normalize object data for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'product_id' => $this->productId,
            'regular_price' => $this->regularPrice,
            'final_price' => $this->finalPrice,
            'is_discounted' => $this->isDiscounted
        ];
    }
}
