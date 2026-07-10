<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\SnippetInterface;

class Snippet
{
    /** @var SnippetInterface */
    private $snippet;

    /**
     * @param SnippetInterface $snippet
     */
    public function __construct(SnippetInterface $snippet)
    {
        $this->snippet = $snippet;
    }

    /**
     * Get is active.
     *
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->snippet->isActive();
    }

    /**
     * Get snippet.
     *
     * @return string
     */
    public function getSnippet(): string
    {
        return $this->snippet->getCodeSnippet();
    }
}
