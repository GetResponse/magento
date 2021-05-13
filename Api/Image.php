<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Image implements JsonSerializable
{
    private $src;
    private $position;

    public function __construct(string $src, int $position)
    {
        $this->src = $src;
        $this->position = $position;
    }

    public function getSrc(): string
    {
        return $this->src;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function jsonSerialize(): array
    {
        return [
            'src' => $this->src,
            'position' => $this->position
        ];
    }
}
