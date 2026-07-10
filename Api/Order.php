<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Order implements JsonSerializable
{
    /** @var int */
    private $id;
    /** @var string */
    private $orderNumber;
    /** @var int */
    private $cartId;
    /** @var string */
    private $contactEmail;
    /** @var Customer */
    private $customer;
    /** @var Line[] */
    private $lines;
    /** @var ?string */
    private $url;
    /** @var float */
    private $totalPrice;
    /** @var float */
    private $totalPriceTax;
    /** @var float */
    private $shippingPrice;
    /** @var string */
    private $currency;
    /** @var string */
    private $status;
    /** @var ?string */
    private $billingStatus;
    /** @var ?Address */
    private $shippingAddress;
    /** @var ?Address */
    private $billingAddress;
    /** @var string */
    private $createdAt;
    /** @var ?string */
    private $updatedAt;

    /**
     * @param int $id
     * @param string $orderNumber
     * @param int $cartId
     * @param string $contactEmail
     * @param Customer $customer
     * @param array $lines
     * @param ?string $url
     * @param float $totalPrice
     * @param float $totalPriceTax
     * @param float $shippingPrice
     * @param string $currency
     * @param string $status
     * @param ?string $billingStatus
     * @param ?Address $shippingAddress
     * @param ?Address $billingAddress
     * @param string $createdAt
     * @param ?string $updatedAt
     */
    public function __construct(
        int $id,
        string $orderNumber,
        int $cartId,
        string $contactEmail,
        Customer $customer,
        array $lines,
        ?string $url,
        float $totalPrice,
        float $totalPriceTax,
        float $shippingPrice,
        string $currency,
        string $status,
        ?string $billingStatus,
        ?Address $shippingAddress,
        ?Address $billingAddress,
        string $createdAt,
        ?string $updatedAt
    ) {
        $this->id = $id;
        $this->orderNumber = $orderNumber;
        $this->cartId = $cartId;
        $this->contactEmail = $contactEmail;
        $this->customer = $customer;
        $this->lines = $lines;
        $this->url = $url;
        $this->totalPrice = $totalPrice;
        $this->totalPriceTax = $totalPriceTax;
        $this->shippingPrice = $shippingPrice;
        $this->currency = $currency;
        $this->status = $status;
        $this->billingStatus = $billingStatus;
        $this->shippingAddress = $shippingAddress;
        $this->billingAddress = $billingAddress;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
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
            'callback_type' => CallbackType::ORDER_UPDATE,
            'id' => $this->id,
            'order_number' => $this->orderNumber,
            'cart_id' => $this->cartId,
            'contact_email' => $this->contactEmail,
            'customer' => $this->customer->jsonSerialize(),
            'lines' => $lines,
            'url' => $this->url,
            'total_price' => $this->totalPrice,
            'total_price_tax' => $this->totalPriceTax,
            'shipping_price' => $this->shippingPrice,
            'currency' => $this->currency,
            'status' => $this->status,
            'billing_status' => $this->billingStatus,
            'shipping_address' => null !== $this->shippingAddress ? $this->shippingAddress->jsonSerialize() : [],
            'billing_address' => null !== $this->billingAddress ? $this->billingAddress->jsonSerialize() : [],
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
