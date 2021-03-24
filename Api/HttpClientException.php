<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Exception;

class HttpClientException extends Exception
{
    public static function createForInvalidCurlResponse(string $response, int $statusCode): self
    {
        return new self($response, $statusCode);
    }

    public static function createFromResponse(array $response): self
    {
        return new self($response['message'], $response['httpStatus']);
    }
}
