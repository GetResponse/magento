<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\SharedKernel;

use RuntimeException;

class Scope
{
    /** @var int */
    private $scopeId;

    /** @param int $scopeId */
    private function __construct(int $scopeId)
    {
        $this->scopeId = $scopeId;
    }

    /**
     * Get scope id.
     */
    public function getScopeId(): int
    {
        return $this->scopeId;
    }

    /**
     * Create from store id.
     *
     * @param mixed $id
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Magento2.Annotation.MethodArguments.NoCommentBlock
    public static function createFromStoreId($id): self
    {
        if (null === $id) {
            throw new RuntimeException('Cannot create Scope from StoreId');
        }

        return new self((int)$id);
    }
}
