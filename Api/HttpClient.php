<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Framework\Serialize\SerializerInterface;

class HttpClient
{
    private const TIMEOUT = 8;

    private $jsonHelper;

    public function __construct(SerializerInterface $jsonHelper)
    {
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @throws HttpClientException
     */
    public function post(string $url, array $params, array $headers = [])
    {
        return $this->sendRequest($url, 'POST', $params, $headers);
    }

    /**
     * @throws HttpClientException
     */
    private function sendRequest(string $url, string $method = 'GET', array $params = [], array $headers = [])
    {
        $json_params = $this->jsonHelper->serialize($params);

        $headers = array_merge($headers, [
            'Content-Type: application/json'
        ]);

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_ENCODING => 'gzip,deflate',
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_HTTPHEADER => $headers,
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $json_params;
        }

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);

        if (false === $response) {
            $error_message = curl_error($curl);
            throw HttpClientException::createForInvalidCurlResponse($error_message);
        }

        curl_close($curl);
        return $response;
    }
}
