<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use RuntimeException;

class WebEventTracking implements SnippetInterface
{
    /** @var bool */
    private $isEnabled;
    /** @var bool */
    private $isFeatureTrackingEnabled;
    /** @var string */
    private $codeSnippet;
    /** @var ?string */
    private $getresponseShopId;

    /**
     * @param bool $isEnabled
     * @param bool $isFeatureTrackingEnabled
     * @param string $codeSnippet
     * @param ?string $getresponseShopId
     */
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

    /**
     * Check active.
     */
    public function isActive(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Get code snippet.
     */
    public function getCodeSnippet(): string
    {
        return $this->codeSnippet;
    }

    /**
     * Get getresponse shop id.
     */
    public function getGetresponseShopId(): ?string
    {
        return $this->getresponseShopId;
    }

    /**
     * Check feature tracking enabled.
     */
    public function isFeatureTrackingEnabled(): bool
    {
        return $this->isFeatureTrackingEnabled;
    }

    /**
     * Handle to array.
     */
    public function toArray(): array
    {
        return [
            'isEnabled' => (int)$this->isEnabled,
            'isFeatureTrackingEnabled' => (int)$this->isFeatureTrackingEnabled,
            'codeSnippet' => $this->codeSnippet,
            'getresponseShopId' => $this->getresponseShopId
        ];
    }

    /**
     * Create from repository.
     *
     * @param array $data
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Magento2.Annotation.MethodArguments.NoCommentBlock
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

    /**
     * Create from request.
     *
     * @param array $data
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Magento2.Annotation.MethodArguments.NoCommentBlock
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
