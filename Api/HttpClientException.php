<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Exception;

class HttpClientException extends Exception
{
    const INVALID_CURL_RESPONSE = '12001';

    public static function createForInvalidCurlResponse(string $errorMessage): self
    {
        return new self($errorMessage, self::INVALID_CURL_RESPONSE);
    }

    public static function createFromResponse(array $response): self
    {
        return new self($response['message'], $response['httpStatus']);
    }
}
