<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class RequestValidationException extends MagentoException
{
    public static function create(string $error): self
    {
        return new self($error);
    }
}
