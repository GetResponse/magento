<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class GetResponseRepositoryException
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class RepositoryException extends \Exception
{
    const INVALID_API_KEY = '12001';
    const INVALID_RULE_ID = '12002';
    const INVALID_RESPONSE_CODE = '12003';
    const CONNECTION_SETTINGS_NOT_FOUND = '12004';

    /**
     * @return RepositoryException
     */
    public static function buildForInvalidApiKey()
    {
        return new self('API Key not found', self::INVALID_API_KEY);
    }

    /**
     * @return RepositoryException
     */
    public static function buildForInvalidRuleId()
    {
        return new self('Rule not found', self::INVALID_RULE_ID);
    }
}
