<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Customer implements JsonSerializable
{
    /** @var int */
    private $id;
    /** @var string */
    private $email;
    /** @var string */
    private $firstName;
    /** @var string */
    private $lastName;
    /** @var bool */
    private $isMarketingAccepted;
    /** @var ?Address */
    private $address;
    /** @var array */
    private $tags;
    /** @var array */
    private $customFields;

    /**
     * @param int $id
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param bool $isMarketingAccepted
     * @param ?Address $address
     * @param array $tags
     * @param array $customFields
     */
    public function __construct(
        int $id,
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

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get first name.
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Get last name.
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Check marketing accepted.
     */
    public function isMarketingAccepted(): bool
    {
        return $this->isMarketingAccepted;
    }

    /**
     * Get address.
     */
    public function getAddress(): ?Address
    {
        return $this->address;
    }

    /**
     * Get tags.
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Get custom fields.
     */
    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    /**
     * Get email.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Serialize object to JSON data.
     */
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
