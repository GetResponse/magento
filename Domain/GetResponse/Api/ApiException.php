<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Api;

use Exception;

class ApiException extends Exception
{
    const INVALID_API_KEY = '12001';
    const INVALID_RESPONSE_CODE = '12003';
    const CONNECTION_SETTINGS_NOT_FOUND = '12004';

    public static function buildForInvalidApiKey(): ApiException
    {
        return new self('API Key not found', self::INVALID_API_KEY);
    }
}
