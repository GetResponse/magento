<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class WebformSettings
{
    private $isEnabled;
    private $url;
    private $webformId;
    private $sidebar;

    public function __construct(
        bool $isEnabled,
        string $url,
        string $webformId,
        string $sidebar
    ) {
        $this->isEnabled = $isEnabled;
        $this->url = $url;
        $this->webformId = $webformId;
        $this->sidebar = $sidebar;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getWebformId(): string
    {
        return $this->webformId;
    }

    public function getSidebar(): string
    {
        return $this->sidebar;
    }

    public function toArray(): array
    {
        return [
            'isEnabled' => (int)$this->isEnabled,
            'url' => $this->url,
            'webformId' => $this->webformId,
            'sidebar' => $this->sidebar
        ];
    }
}
