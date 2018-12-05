<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto;

use GetResponse\GetResponseIntegration\Domain\GetResponse\GetResponseDomainException;

/**
 * Class InvalidPrefixException
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto
 */
class InvalidPrefixException extends GetResponseDomainException
{
    /**
     * @param string $prefix
     * @return InvalidPrefixException
     */
    public static function createForInvalidPrefix($prefix)
    {
        return new self(sprintf('DTO Mapping prefix %s from request is invalid', $prefix));
    }
}