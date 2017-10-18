<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class GetResponseRepositoryException
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class GetResponseRepositoryException extends \Exception
{
    const INVALID_API_KEY = '12001';
    const INVALID_RULE_ID = '12002';

    /**
     * @return GetResponseRepositoryException
     */
    public static function buildForInvalidApiKey()
    {
        return new self('API Key not found', self::INVALID_API_KEY);
    }

    /**
     * @return GetResponseRepositoryException
     */
    public static function buildForInvalidRuleId()
    {
        return new self('Rule not found', self::INVALID_RULE_ID);
    }
}
