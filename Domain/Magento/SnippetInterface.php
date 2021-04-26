<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

interface SnippetInterface
{
    public function isActive(): bool;
    public function getCodeSnippet(): string;
}
