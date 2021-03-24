<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Category implements JsonSerializable
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
        bool $isDefault = false,
        ?string $url = null
    ) {
        $this->id = $id;
        $this->parentId = $parentId;
        $this->name = $name;
        $this->isDefault = $isDefault;
        $this->url = $url;
    }

    public function toApiRequest(): array
    {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parentId,
            'name' => $this->name,
            'is_default' => $this->isDefault,
            'url' => $this->url,
        ];
    }
}
