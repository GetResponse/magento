<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use RuntimeException;

class LiveSynchronization
{
    private $isActive;

    public function __construct(bool $isActive)
    {
        $this->isActive = $isActive;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public static function createFromRequest(array $data): self
    {
        if (!isset($data['liveSynchronization'])) {
            throw new RuntimeException('incorrect LiveSynchronization params');
        }

        return new self($data['liveSynchronization']['isActive']);
    }

    public static function createFromRepository($data): self
    {
        return new self(null !== $data ? (bool) $data : false);
    }
}
