<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

use Exception;

/**
 * Class EcommerceSettingsException
 * @package GetResponse\GetResponseIntegration\Domain\Magento
 */
class ValidationException extends Exception
{
    /**
     * @param string $message
     * @throws ValidationException
     */
    public static function createForInvalidValue($message)
    {
        throw new self($message);
    }
}
