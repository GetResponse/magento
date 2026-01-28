<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Exception;

class HttpClientException extends Exception
{
    // phpcs:ignore
    public static function createForInvalidCurlResponse(string $response, int $statusCode): self
    {
        return new self($response, $statusCode);
    }
}
