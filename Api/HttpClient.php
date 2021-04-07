<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\SerializerInterface;
use JsonSerializable;

class HttpClient
{
    public const POST = 'POST';
    public const GET = 'GET';

    private $curl;
    private $jsonHelper;

    public function __construct(Curl $curl, SerializerInterface $jsonHelper)
    {
        $this->curl = $curl;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @throws HttpClientException
     */
    public function post(string $url, JsonSerializable $object): string
    {
        print "<pre>";
        print_r($url);
        print PHP_EOL;
        print_r($object);
        print "</pre>";
        die;
        return $this->sendRequest($url, 'POST', $object);
    }

    /**
     * @throws HttpClientException
     */
    private function sendRequest(
        string $url,
        string $method = self::GET,
        JsonSerializable $object = null
    ): string {
        $this->curl->addHeader('Content-Type', 'application/json');

        $method === self::POST ? $this->curl->post($url, $this->jsonHelper->serialize($object)) : $this->curl->get($url);

        if (299 < $this->curl->getStatus()) {
            throw HttpClientException::createForInvalidCurlResponse($this->curl->getBody(), $this->curl->getStatus());
        }

        return $this->curl->getBody();
    }
}
