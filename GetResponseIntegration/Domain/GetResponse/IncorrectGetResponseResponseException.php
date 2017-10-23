<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class IncorrectGetResponseResponseException
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class IncorrectGetResponseResponseException extends GetResponseRepositoryException
{
    /**
     * @return IncorrectGetResponseResponseException
     */
    public static function buildForInvalidResponseCode()
    {
        return new self('API Key not found', self::INVALID_RESPONSE_CODE);
    }
}
