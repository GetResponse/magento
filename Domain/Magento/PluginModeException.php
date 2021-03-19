<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class PluginModeException extends MagentoException
{
    public static function createForInvalidPluginMode(string $error): self
    {
        return new self($error, self::INVALID_PLUGIN_MODE_ERROR_CODE);
    }
}
