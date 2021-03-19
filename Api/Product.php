<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

class Product
{
    private $id;
    private $name;
    private $type;
    private $url;
    private $vendor;
    /** @var Category[] */
    private $categories;
    /** @var Variant[] */
    private $variants;
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
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function toApiRequest(): array
    {
        $categories = [];
        foreach ($this->categories as $category) {
            $categories[] = $category->toApiRequest();
        }

        $variants = [];
        foreach ($this->variants as $variant) {
            $variants[] = $variant->toApiRequest();
        }

        return [
            'callable_type' => 'products/update',
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'url' => $this->url,
            'vendor' => $this->vendor,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'categories' => $categories,
            'variants' => $variants,
        ];
    }
}
