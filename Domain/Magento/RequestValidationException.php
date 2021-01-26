<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use Exception;

class RequestValidationException extends Exception
{
    public static function create(string $error): self
    {
        return new self($error);
    }
}
