<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class RequestValidationException extends MagentoException
{
    /**
     * Create request validation exception.
     *
     * @param string $error
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Magento2.Annotation.MethodArguments.NoCommentBlock
    public static function create(string $error): self
    {
        return new self($error);
    }
}
