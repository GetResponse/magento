<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Image implements JsonSerializable
{
    /** @var string */
    private $src;
    /** @var int */
    private $position;

    /**
     * @param string $src
     * @param int $position
     */
    public function __construct(string $src, int $position)
    {
        $this->src = $src;
        $this->position = $position;
    }

    /**
     * Get src.
     */
    public function getSrc(): string
    {
        return $this->src;
    }

    /**
     * Get position.
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Serialize object to JSON data.
     */
    public function jsonSerialize(): array
    {
        return [
            'src' => $this->src,
            'position' => $this->position
        ];
    }
}
