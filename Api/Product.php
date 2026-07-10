<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Product implements JsonSerializable
{
    public const STATUS_PUBLISH = 'publish';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_DELETED = 'deleted';

    /** @var int */
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $type;
    /** @var string */
    private $url;
    /** @var string */
    private $vendor;
    /** @var Category[] */
    private $categories;
    /** @var Variant[] */
    private $variants;
    /** @var string */
    private $status;
    /** @var string */
    private $createdAt;
    /** @var ?string */
    private $updatedAt;

    /**
     * @param int $id
     * @param string $name
     * @param string $type
     * @param string $url
     * @param string $vendor
     * @param array $categories
     * @param array $variants
     * @param string $status
     * @param string $createdAt
     * @param ?string $updatedAt
     */
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

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get url.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get vendor.
     */
    public function getVendor(): string
    {
        return $this->vendor;
    }

    /**
     * Get categories.
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Get variants.
     */
    public function getVariants(): array
    {
        return $this->variants;
    }

    /**
     * Get status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Get created at.
     */
    public function getCreatedAt(): string
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
