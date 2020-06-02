<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use Exception;

class ConnectionSettingsException extends Exception
{
    public static function createForIncorrectSettings(): ConnectionSettingsException
    {
        return new self('Incorrect connection settings');
    }
}
