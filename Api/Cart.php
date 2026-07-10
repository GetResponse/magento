<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\Visitor;
use JsonSerializable;

class Cart implements JsonSerializable
{
    /** @var int */
    private $id;
    /** @var ?Customer */
    private $customer;
    /** @var ?Visitor */
    private $visitor;
    /** @var array */
    private $lines;
    /** @var float */
    private $totalPrice;
    /** @var float */
    private $totalTaxPrice;
    /** @var string */
    private $currency;
    /** @var string */
    private $url;
    /** @var ?string */
    private $createdAt;
    /** @var ?string */
    private $updatedAt;

    /**
     * @param int $id
     * @param ?Customer $customer
     * @param ?Visitor $visitor
     * @param array $lines
     * @param float $totalPrice
     * @param float $totalTaxPrice
     * @param string $currency
     * @param string $url
     * @param ?string $createdAt
     * @param ?string $updatedAt
     */
    public function __construct(
        int $id,
        ?Customer $customer,
        ?Visitor $visitor,
        array $lines,
        float $totalPrice,
        float $totalTaxPrice,
        string $currency,
        string $url,
        ?string $createdAt,
        ?string $updatedAt
    ) {
        $this->id = $id;
        $this->customer = $customer;
        $this->visitor = $visitor;
        $this->lines = $lines;
        $this->totalPrice = $totalPrice;
        $this->totalTaxPrice = $totalTaxPrice;
        $this->currency = $currency;
        $this->url = $url;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get customer.
     */
    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    /**
     * Get lines.
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * Get total price.
     */
    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    /**
     * Get total tax price.
     */
    public function getTotalTaxPrice(): float
    {
        return $this->totalTaxPrice;
    }

    /**
     * Get currency.
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Get url.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get created at.
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Get updated at.
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Serialize object to JSON data.
     */
    public function jsonSerialize(): array
    {
        $lines = [];
        foreach ($this->lines as $line) {
            $lines[] = $line->jsonSerialize();
        }

        return [
            'callback_type' => CallbackType::CHECKOUT_UPDATE,
            'id' => $this->id,
            'contact_email' => $this->customer !== null ? $this->customer->getEmail() : null,
            'customer' => $this->customer !== null ? $this->customer->jsonSerialize() : [],
            'visitor_uuid' => $this->visitor !== null ? $this->visitor->getVisitorUuid() : null,
            'lines' => $lines,
            'total_price' => $this->totalPrice,
            'total_price_tax' => $this->totalTaxPrice,
            'currency' => $this->currency,
            'url' => $this->url,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Check valuable.
     */
    public function isValuable(): bool
    {
        return $this->id !== 0 && ($this->customer !== null || $this->visitor !== null);
    }
}
