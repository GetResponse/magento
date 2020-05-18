<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto;

use GetResponse\GetResponseIntegration\Domain\GetResponse\GetResponseDomainException;

class InvalidPrefixException extends GetResponseDomainException
{
    public static function createForInvalidPrefix(string $prefix): InvalidPrefixException
    {
        return new self(sprintf('DTO Mapping prefix %s from request is invalid', $prefix));
    }
}
