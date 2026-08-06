<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

/**
 * Represents a single sale-price candidate considered by ProductFactory::getSalesPrice().
 *
 * Carries the price together with the dates describing the period during
 * which exactly that price is valid (e.g. base price has no dates, special
 * price carries its own from/to dates, a catalog price rule carries its own
 * from/to dates).
 */
class SalePriceCandidate
{
    /** @var float */
    private $price;
    /** @var ?string */
    private $from;
    /** @var ?string */
    private $to;

    /**
     * @param float $price
     * @param ?string $from
     * @param ?string $to
     */
    public function __construct(float $price, ?string $from, ?string $to)
    {
        $this->price = $price;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * Get from date.
     *
     * @return ?string
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * Get to date.
     *
     * @return ?string
     */
    public function getTo(): ?string
    {
        return $this->to;
    }
}
