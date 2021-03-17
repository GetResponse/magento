<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

class Category
{
    private $id;
    private $parentId;
    private $name;
    private $isDefault;
    private $url;

    public function __construct(
        int $id,
        int $parentId,
        string $name,
        bool $isDefault,
        string $url
    ) {
        $this->id = $id;
        $this->parentId = $parentId;
        $this->name = $name;
        $this->isDefault = $isDefault;
        $this->url = $url;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
