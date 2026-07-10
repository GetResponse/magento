<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Subscriber implements JsonSerializable
{
    /** @var int */
    private $id;
    /** @var string */
    private $email;
    /** @var string */
    private $name;
    /** @var bool */
    private $isMarketingAccepted;
    /** @var array */
    private $tags;
    /** @var array */
    private $customFields;

    /**
     * @param int $id
     * @param string $email
     * @param string $name
     * @param bool $isMarketingAccepted
     * @param array $tags
     * @param array $customFields
     */
    public function __construct(
        int $id,
        string $email,
        string $name,
        bool $isMarketingAccepted,
        array $tags,
        array $customFields
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->isMarketingAccepted = $isMarketingAccepted;
        $this->tags = $tags;
        $this->customFields = $customFields;
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
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Check marketing accepted.
     */
    public function isMarketingAccepted(): bool
    {
        return $this->isMarketingAccepted;
    }

    /**
     * Get name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get tags.
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Serialize object to JSON data.
     */
    public function jsonSerialize(): array
    {
        return [
            'callback_type' => CallbackType::SUBSCRIBERS_UPDATE,
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'accepts_marketing' => $this->isMarketingAccepted,
            'tags' => $this->tags,
            'customFields' => $this->customFields
        ];
    }
}
