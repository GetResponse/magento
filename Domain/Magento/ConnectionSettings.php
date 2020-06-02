<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class ConnectionSettings
{
    private $apiKey;
    private $url;
    private $domain;

    public function __construct(string $apiKey, string $url, string $domain)
    {
        $this->apiKey = $apiKey;
        $this->url = $url;
        $this->domain = $domain;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function toArray(): array
    {
        return [
            'apiKey' => $this->apiKey,
            'url' => $this->url,
            'domain' => $this->domain
        ];
    }
}
