<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

class Image
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
}
