<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\SnippetInterface;

class SnippetPresenter
{
    private $snippet;

    public function __construct(SnippetInterface $snippet)
    {
        $this->snippet = $snippet;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->snippet->isActive();
    }

    /**
     * @return string
     */
    public function getSnippet(): string
    {
        return $this->snippet->getCodeSnippet();
    }
}
