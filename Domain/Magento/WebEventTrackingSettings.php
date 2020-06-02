<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class WebEventTrackingSettings
{
    private $isEnabled;
    private $isFeatureTrackingEnabled;
    private $codeSnippet;

    public function __construct(
        bool $isEnabled,
        bool $isFeatureTrackingEnabled,
        string $codeSnippet
    ) {
        $this->isEnabled = $isEnabled;
        $this->isFeatureTrackingEnabled = $isFeatureTrackingEnabled;
        $this->codeSnippet = $codeSnippet;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function getCodeSnippet(): string
    {
        return $this->codeSnippet;
    }

    public function toArray(): array
    {
        return [
            'isEnabled' => (int)$this->isEnabled,
            'isFeatureTrackingEnabled' => (int)$this->isFeatureTrackingEnabled,
            'codeSnippet' => $this->codeSnippet
        ];
    }

    public function isFeatureTrackingEnabled(): bool
    {
        return $this->isFeatureTrackingEnabled;
    }
}
