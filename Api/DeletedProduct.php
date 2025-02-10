<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class DeletedProduct implements JsonSerializable
{
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'callback_type' => CallbackType::PRODUCT_DELETE,
            'id' => $this->id,
        ];
    }
}
