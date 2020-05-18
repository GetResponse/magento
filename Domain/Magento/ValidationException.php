<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use Exception;

class ValidationException extends Exception
{
    public static function createForInvalidValue($message): ValidationException
    {
        return new self($message);
    }
}
