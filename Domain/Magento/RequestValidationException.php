<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class RequestValidationException extends MagentoException
{
    public static function create(string $error): self
    {
        return new self($error);
    }

    public static function createForMissingScope(): self
    {
        return new self('Missing scope.', self::MISSING_SCOPE_ERROR_CODE);
    }

    public static function createForIncorrectScope(): self
    {
        return new self('Incorrect scope.', self::INCORRECT_SCOPE_ERROR_CODE);
    }
}
