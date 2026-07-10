<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

interface SnippetInterface
{
    /**
     * Check active.
     */
    public function isActive(): bool;
    /**
     * Get code snippet.
     */
    public function getCodeSnippet(): string;
}
