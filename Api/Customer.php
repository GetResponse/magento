<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Customer implements JsonSerializable
{
    private $id;
    private $email;
    private $firstName;
    private $lastName;
    private $isMarketingAccepted;
    private $address;
    private $tags;
    private $customFields;

    public function __construct(
        ?int $id,
        string $email,
        string $firstName,
        string $lastName,
        bool $isMarketingAccepted,
        ?Address $address,
        array $tags,
        array $customFields
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->isMarketingAccepted = $isMarketingAccepted;
        $this->address = $address;
        $this->tags = $tags;
        $this->customFields = $customFields;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function isMarketingAccepted(): bool
    {
        return $this->isMarketingAccepted;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function jsonSerialize(): array
    {
        return [
            'callback_type' => CallbackType::CUSTOMER_UPDATE,
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'accepts_marketing' => $this->isMarketingAccepted,
            'address' => $this->address ? $this->address->jsonSerialize() : null,
            'tags' => $this->tags,
            'customFields' => $this->customFields
        ];
    }
}
