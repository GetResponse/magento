<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

class RequestHeaders
{
    private $shopDomain;
    private $hmac;
    private $timestamp;
    private $platformVersion;
    private $phpVersion;
    private $pluginVersion;

    public function __construct(
        string $shopDomain,
        string $hmac,
        string $timestamp,
        string $platformVersion,
        string $phpVersion,
        string $pluginVersion
    ) {
        $this->shopDomain = $shopDomain;
        $this->hmac = $hmac;
        $this->timestamp = $timestamp;
        $this->platformVersion = $platformVersion;
        $this->phpVersion = $phpVersion;
        $this->pluginVersion = $pluginVersion;
    }

    public function toArray(): array
    {
        return [
            'Content-Type' => 'application/json',
            'X-Shop-Domain' => $this->shopDomain,
            'X-Hmac-Sha256' => $this->hmac,
            'X-Timestamp' => $this->timestamp,
            'X-Platform-Version' => $this->platformVersion,
            'X-PHP-Version' => $this->phpVersion,
            'X-Plugin-Version' => $this->pluginVersion,
        ];
    }
}
