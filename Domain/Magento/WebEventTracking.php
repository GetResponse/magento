<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use RuntimeException;

class WebEventTracking implements SnippetInterface
{
    private $isEnabled;
    private $codeSnippet;

    public function __construct(bool $isEnabled, string $codeSnippet)
    {
        $this->isEnabled = $isEnabled;
        $this->codeSnippet = $codeSnippet;
    }

    public function isActive(): bool
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
            'codeSnippet' => $this->codeSnippet
        ];
    }

    public static function createFromRepository(array $data): WebEventTracking
    {
        if (empty($data)) {
            return new WebEventTracking(
                false,
                ''
            );
        }

        return new WebEventTracking((bool)$data['isEnabled'], $data['codeSnippet']);
    }

    public static function createFromRequest(array $data): WebEventTracking
    {
        if (!isset($data['web_event_tracking'])) {
            throw new RuntimeException('incorrect TrackingCode params');
        }

        return new WebEventTracking(
            (bool)$data['web_event_tracking']['is_active'],
            $data['web_event_tracking']['snippet']
        );
    }

    public static function createFromArray(array $data): WebEventTracking
    {
        return new WebEventTracking((bool)$data['isEnabled'], $data['codeSnippet']);
    }
}
