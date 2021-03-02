<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use Exception;

class PluginModeException extends Exception
{
    public static function createForInvalidPluginMode(string $error): self
    {
        return new self($error, 405);
    }
}
