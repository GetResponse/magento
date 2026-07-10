<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Category implements JsonSerializable
{
    /** @var int */
    private $id;
    /** @var int */
    private $parentId;
    /** @var string */
    private $name;
    /** @var bool */
    private $isDefault;
    /** @var ?string */
    private $url;

    /**
     * @param int $id
     * @param int $parentId
     * @param string $name
     * @param bool $isDefault
     * @param ?string $url
     */
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

    /**
     * Serialize object to JSON data.
     */
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
