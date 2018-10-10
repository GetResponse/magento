<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

use Exception;

/**
 * Class ConnectionSettingsException
 * @package GetResponse\GetResponseIntegration\Domain\Magento
 */
class ConnectionSettingsException extends Exception
{
    /**
     * @return ConnectionSettingsException
     */
    public static function createForIncorrectSettings()
    {
        return new self('Incorrect connection settings');
    }
}
