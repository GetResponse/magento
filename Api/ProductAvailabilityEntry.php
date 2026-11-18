<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

/**
 * Salability of a product on the page of one product: its own page or the page of a configurable parent.
 */
class ProductAvailabilityEntry implements JsonSerializable
{
    /** @var int */
    private $viaProductId;
    /** @var bool */
    private $isSalable;

    /**
     * @param int $viaProductId
     * @param bool $isSalable
     */
    public function __construct(int $viaProductId, bool $isSalable)
    {
        $this->viaProductId = $viaProductId;
        $this->isSalable = $isSalable;
    }

    /**
     * Get ID of the product the product is sold via: its own ID or the ID of a configurable parent.
     *
     * @return int
     */
    public function getViaProductId(): int
    {
        return $this->viaProductId;
    }

    /**
     * Whether the product can currently be bought via this product in the requested store.
     *
     * @return bool
     */
    public function getIsSalable(): bool
    {
        return $this->isSalable;
    }

    /**
     * Normalize object data for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'via_product_id' => $this->viaProductId,
            'is_salable' => $this->isSalable
        ];
    }
}
