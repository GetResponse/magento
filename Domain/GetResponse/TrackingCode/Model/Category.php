<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model;

class Category
{
    /** @var int */
    private $id;
    /** @var string */
    private $name;

    /**
     * @param int $id
     * @param string $name
     */
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Handle to array.
     */
    public function toArray(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name
        ];
    }
}
