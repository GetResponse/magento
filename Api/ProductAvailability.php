<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class ProductAvailability implements JsonSerializable
{
    /** @var int */
    private $productId;
    /** @var ProductAvailabilityEntry[] */
    private $salability;

    /**
     * @param int $productId
     * @param ProductAvailabilityEntry[] $salability
     */
    public function __construct(int $productId, array $salability)
    {
        $this->productId = $productId;
        $this->salability = $salability;
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
     * Get salability of the product on every product page it is sold via.
     *
     * @return \GetResponse\GetResponseIntegration\Api\ProductAvailabilityEntry[]
     */
    public function getSalability(): array
    {
        return $this->salability;
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
            'salability' => array_map(function (ProductAvailabilityEntry $entry) {
                return $entry->jsonSerialize();
            }, $this->salability)
        ];
    }
}
