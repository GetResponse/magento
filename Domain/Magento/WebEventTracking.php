<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use RuntimeException;

class WebEventTracking implements SnippetInterface
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
            'isFeatureTrackingEnabled' => (int)$this->isFeatureTrackingEnabled,
            'codeSnippet' => $this->codeSnippet
        ];
    }

    public function isFeatureTrackingEnabled(): bool
    {
        return $this->isFeatureTrackingEnabled;
    }

    public static function createFromRepository(array $data): WebEventTracking
    {
        if (empty($data)) {
            return new WebEventTracking(
                false,
                false,
                ''
            );
        }

        return new WebEventTracking(
            (bool)$data['isEnabled'],
            (bool)$data['isFeatureTrackingEnabled'],
            $data['codeSnippet']
        );
    }

    public static function createFromRequest(array $data): WebEventTracking
    {
        if (!isset($data['trackingCode'])) {
            throw new RuntimeException('incorrect TrackingCode params');
        }

        return new WebEventTracking(
            (bool)$data['trackingCode']['isActive'],
            (bool)$data['trackingCode']['isFeatureTrackingActive'],
            $data['trackingCode']['codeSnippet']
        );
    }

    public static function createFromArray(array $data): WebEventTracking
    {
        return new WebEventTracking(
            (bool)$data['isEnabled'],
            (bool)$data['isFeatureTrackingEnabled'],
            $data['codeSnippet']
        );
    }
}
