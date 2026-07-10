<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Exception;

class HttpClientException extends Exception
{
    /**
     * Create for invalid curl response.
     *
     * @param string $response
     * @param int $statusCode
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Magento2.Annotation.MethodArguments.NoCommentBlock
    public static function createForInvalidCurlResponse(string $response, int $statusCode): self
    {
        return new self($response, $statusCode);
    }
}
