<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Helper\Config;
use JsonSerializable;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Url\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class HttpClient
{
    public const POST = 'POST';
    public const GET = 'GET';
    public const HTTP_TIMEOUT = 3;
    public const HTTP_CONNECTION_TIMEOUT = 2;

    /** @var Curl */
    private $curl;
    /** @var SerializerInterface */
    private $jsonHelper;
    /** @var StoreManagerInterface */
    private $storeManager;
    /** @var PlatformVersionProvider */
    private $platformVersionProvider;

    /**
     * @param Curl $curl
     * @param SerializerInterface $jsonHelper
     * @param StoreManagerInterface $storeManager
     * @param PlatformVersionProvider $platformVersionProvider
     */
    public function __construct(
        Curl $curl,
        SerializerInterface $jsonHelper,
        StoreManagerInterface $storeManager,
        PlatformVersionProvider $platformVersionProvider
    ) {
        $this->curl = $curl;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->platformVersionProvider = $platformVersionProvider;
    }

    /**
     * Handle post.
     *
     * @param string $url
     * @param JsonSerializable $object
     * @throws HttpClientException
     */
    public function post(string $url, JsonSerializable $object): string
    {
        return $this->sendRequest($url, self::POST, $object);
    }

    /**
     * Handle send request.
     *
     * @param string $url
     * @param string $method
     * @param JsonSerializable $object
     * @throws HttpClientException
     */
    private function sendRequest(string $url, string $method, JsonSerializable $object): string
    {
        $this->curl->setHeaders($this->buildHeaders($object)->toArray());
        $this->curl->setOptions([
            CURLOPT_TIMEOUT => self::HTTP_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::HTTP_CONNECTION_TIMEOUT,
        ]);

        $method === self::POST
            ? $this->curl->post($url, $this->jsonHelper->serialize($object))
            : $this->curl->get($url);

        if (299 < $this->curl->getStatus()) {
            throw HttpClientException::createForInvalidCurlResponse($this->curl->getBody(), $this->curl->getStatus());
        }

        return $this->curl->getBody();
    }

    /**
     * Create hmac.
     *
     * @param JsonSerializable $object
     */
    private function createHmac(JsonSerializable $object): string
    {
        return base64_encode(
            hash_hmac(
                'sha256',
                $this->jsonHelper->serialize($object->jsonSerialize()),
                Config::API_APP_SECRET,
                true
            )
        );
    }

    /**
     * Build headers.
     *
     * @param JsonSerializable $object
     */
    private function buildHeaders(JsonSerializable $object): RequestHeaders
    {
        /** @var ScopeInterface $store */
        $store = $this->storeManager->getStore();
        return new RequestHeaders(
            $store->getBaseUrl(),
            $this->createHmac($object),
            date('Y-m-d H:i:s.') . gettimeofday()['usec'],
            $this->platformVersionProvider->getMagentoVersion(),
            $this->platformVersionProvider->getPhpVersion(),
            $this->platformVersionProvider->getPluginVersion()
        );
    }
}
