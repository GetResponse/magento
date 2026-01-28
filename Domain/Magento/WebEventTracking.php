<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use RuntimeException;

class WebEventTracking implements SnippetInterface
{
    private $isEnabled;
    private $isFeatureTrackingEnabled;
    private $codeSnippet;
    private $getresponseShopId;

    public function __construct(
        bool $isEnabled,
        bool $isFeatureTrackingEnabled,
        string $codeSnippet,
        ?string $getresponseShopId
    ) {
        $this->isEnabled = $isEnabled;
        $this->isFeatureTrackingEnabled = $isFeatureTrackingEnabled;
        $this->codeSnippet = $codeSnippet;
        $this->getresponseShopId = $getresponseShopId;
    }

    public function isActive(): bool
    {
        return $this->isEnabled;
    }

    public function getCodeSnippet(): string
    {
        return $this->codeSnippet;
    }

    public function getGetresponseShopId(): ?string
    {
        return $this->getresponseShopId;
    }

    public function isFeatureTrackingEnabled(): bool
    {
        return $this->isFeatureTrackingEnabled;
    }

    public function toArray(): array
    {
        return [
            'isEnabled' => (int)$this->isEnabled,
            'isFeatureTrackingEnabled' => (int)$this->isFeatureTrackingEnabled,
            'codeSnippet' => $this->codeSnippet,
            'getresponseShopId' => $this->getresponseShopId
        ];
    }

    // phpcs:ignore
    public static function createFromRepository(array $data): self
    {
        if (empty($data)) {
            return new WebEventTracking(
                false,
                false,
                '',
                null
            );
        }

        return new WebEventTracking(
            (bool)$data['isEnabled'],
            (bool) $data['isFeatureTrackingEnabled'],
            $data['codeSnippet'],
            isset($data['getresponseShopId']) ? $data['getresponseShopId'] : null
        );
    }

    // phpcs:ignore
    public static function createFromRequest(array $data): self
    {
        if (!isset($data['web_event_tracking'])) {
            throw new RuntimeException('incorrect TrackingCode params');
        }

        return new WebEventTracking(
            (bool)$data['web_event_tracking']['is_active'],
            true,
            $data['web_event_tracking']['snippet'],
            $data['web_event_tracking']['getresponseShopId']
        );
    }
}
