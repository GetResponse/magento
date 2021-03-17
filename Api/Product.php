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
    private $categories;
    private $variants;

    public function __construct(
        int $id,
        string $name,
        string $type,
        string $url,
        string $vendor,
        array $categories,
        array $variants
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->url = $url;
        $this->vendor = $vendor;
        $this->categories = $categories;
        $this->variants = $variants;
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
}
