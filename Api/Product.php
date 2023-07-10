<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Product implements JsonSerializable
{
    public const STATUS_PUBLISH = 'publish';
    public const STATUS_DRAFT = 'draft';

    private $id;
    private $name;
    private $type;
    private $url;
    private $vendor;
    /** @var Category[] */
    private $categories;
    /** @var Variant[] */
    private $variants;
    private $status;
    private $createdAt;
    private $updatedAt;

    public function __construct(
        int $id,
        string $name,
        string $type,
        string $url,
        string $vendor,
        array $categories,
        array $variants,
        string $status,
        string $createdAt,
        ?string $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->url = $url;
        $this->vendor = $vendor;
        $this->categories = $categories;
        $this->variants = $variants;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getVendor(): string
    {
        return $this->vendor;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getVariants(): array
    {
        return $this->variants;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function jsonSerialize(): array
    {
        $categories = [];
        foreach ($this->categories as $category) {
            $categories[] = $category->jsonSerialize();
        }

        $variants = [];
        foreach ($this->variants as $variant) {
            $variants[] = $variant->jsonSerialize();
        }

        return [
            'callback_type' => CallbackType::PRODUCT_UPDATE,
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'url' => $this->url,
            'vendor' => $this->vendor,
            'variants' => $variants,
            'categories' => $categories,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}
