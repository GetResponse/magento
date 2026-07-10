<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class DeletedProduct implements JsonSerializable
{
    /** @var int */
    private $id;

    /**
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * Serialize object to JSON data.
     */
    public function jsonSerialize(): array
    {
        return [
            'callback_type' => CallbackType::PRODUCT_DELETE,
            'id' => $this->id,
        ];
    }
}
