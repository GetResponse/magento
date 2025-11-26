<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Helper\Config;
use JsonSerializable;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\ProductMetadataInterface;

class HttpClient
{
    public const POST = 'POST';
    public const GET = 'GET';

    private $curl;
    private $jsonHelper;
    private $storeManager;
    private $moduleList;
    private $productMetadata;
    private $platformVersionProvider;

    public function __construct(
        Curl $curl,
        SerializerInterface $jsonHelper,
        StoreManagerInterface $storeManager,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $productMetadata,
        PlatformVersionProvider $platformVersionProvider,
    )
    {
        $this->curl = $curl;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->moduleList = $moduleList;
        $this->productMetadata = $productMetadata;
        $this->platformVersionProvider = $platformVersionProvider;
    }

    /**
     * @throws HttpClientException
     */
    public function post(string $url, JsonSerializable $object): string
    {
        return $this->sendRequest($url, self::POST, $object);
    }

    /**
     * @throws HttpClientException
     */
    private function sendRequest(string $url, string $method, JsonSerializable $object): string
    {
        $this->curl->setHeaders($this->buildHeaders($object)->toArray());

        $method === self::POST ? $this->curl->post($url, $this->jsonHelper->serialize($object)) : $this->curl->get($url);

        if (299 < $this->curl->getStatus()) {
            throw HttpClientException::createForInvalidCurlResponse($this->curl->getBody(), $this->curl->getStatus());
        }

        return $this->curl->getBody();
    }

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

    private function buildHeaders(JsonSerializable $object): RequestHeaders
    {
        return new RequestHeaders(
            $this->storeManager->getStore()->getBaseUrl(),
            $this->createHmac($object),
            date('Y-m-d H:i:s.') . gettimeofday()['usec'],
            $this->platformVersionProvider->getMagentoVersion(),
            $this->platformVersionProvider->getPhpVersion(),
            $this->platformVersionProvider->getPluginVersion()
        );
    }
}
