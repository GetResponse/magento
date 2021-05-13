<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Subscriber implements JsonSerializable
{
    private $id;
    private $email;
    private $name;
    private $isMarketingAccepted;
    private $tags;
    private $customFields;

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

    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isMarketingAccepted(): bool
    {
        return $this->isMarketingAccepted;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

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
