<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

class RequestHeaders
{
    /** @var string */
    private $shopDomain;
    /** @var string */
    private $hmac;
    /** @var string */
    private $timestamp;
    /** @var string */
    private $platformVersion;
    /** @var string */
    private $phpVersion;
    /** @var string */
    private $pluginVersion;

    /**
     * @param string $shopDomain
     * @param string $hmac
     * @param string $timestamp
     * @param string $platformVersion
     * @param string $phpVersion
     * @param string $pluginVersion
     */
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

    /**
     * Handle to array.
     */
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
