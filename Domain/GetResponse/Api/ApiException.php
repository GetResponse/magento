<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Api;

use Exception;

/**
 * Class ApiException
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Api
 */
class ApiException extends Exception
{
    const INVALID_API_KEY = '12001';
    const INVALID_RESPONSE_CODE = '12003';
    const CONNECTION_SETTINGS_NOT_FOUND = '12004';

    /**
     * @return ApiException
     */
    public static function buildForInvalidApiKey()
    {
        return new self('API Key not found', self::INVALID_API_KEY);
    }
}
