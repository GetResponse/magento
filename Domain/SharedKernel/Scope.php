<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\SharedKernel;

use RuntimeException;

class Scope
{
    private $scopeId;

    private function __construct(int $scopeId)
    {
        $this->scopeId = $scopeId;
    }

    public function getScopeId(): int
    {
        return $this->scopeId;
    }

    // phpcs:ignore
    public static function createFromStoreId($id): self
    {
        if (null === $id) {
            throw new RuntimeException('Cannot create Scope from StoreId');
        }

        return new self((int)$id);
    }
}
