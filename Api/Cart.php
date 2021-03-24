<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Cart implements JsonSerializable
{
    private $id;
    private $customer;
    /** @var Line[] */
    private $lines;
    private $totalPrice;
    private $totalTaxPrice;
    private $currency;
    private $url;
    private $createdAt;
    private $updatedAt;

    public function __construct(
        int $id,
        Customer $customer,
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
        $this->lines = $lines;
        $this->totalPrice = $totalPrice;
        $this->totalTaxPrice = $totalTaxPrice;
        $this->currency = $currency;
        $this->url = $url;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function jsonSerialize(): array
    {
        $lines = [];
        foreach ($this->lines as $line) {
            $lines[] = $line->jsonSerialize();
        }

        return [
            'callback_type' => 'cart/update',
            'id' => $this->id,
            'contact_email' => $this->customer->getEmail(),
            'customer' => $this->customer->jsonSerialize(),
            'lines' => $lines,
            'total_price' => $this->totalPrice,
            'total_price_tax' => $this->totalTaxPrice,
            'currency' => $this->currency,
            'url' => $this->url,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
