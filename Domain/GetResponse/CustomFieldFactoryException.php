<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class CustomFieldFactoryException
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class CustomFieldFactoryException extends \Exception
{
    /**
     * @param string $message
     * @return CustomFieldFactoryException
     */
    public static function createWithMessage($message)
    {
        return new self($message);
    }
}