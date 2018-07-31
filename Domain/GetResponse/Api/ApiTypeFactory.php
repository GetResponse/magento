<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Api;

use GrShareCode\Api\ApiType;
use GrShareCode\Api\ApiTypeException;

/**
 * Class ApiTypeFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Api
 */
class ApiTypeFactory
{
    /**
     * @param string $domainUrl
     * @return ApiType
     * @throws ApiTypeException
     */
    public static function createFromDomainUrl($domainUrl)
    {
        switch ($domainUrl) {
            case '':
                return ApiType::createForSMB();
            case AccountType::API_URL_MX_PL:
                return ApiType::createForMxPl($domainUrl);
            case AccountType::API_URL_MX_US:
                return ApiType::createForMxPl($domainUrl);
        }

        throw ApiTypeException::createForInvalidApiType();
    }
}