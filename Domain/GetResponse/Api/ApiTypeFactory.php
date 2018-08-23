<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettings;
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
    public static function createFromConnectionSettings(ConnectionSettings $connectionSettings)
    {
        switch ($connectionSettings->getUrl()) {
            case '':
                return ApiType::createForSMB();
            case AccountType::API_URL_MX_PL:
                return ApiType::createForMxPl($connectionSettings->getDomain());
            case AccountType::API_URL_MX_US:
                return ApiType::createForMxUs($connectionSettings->getDomain());
        }

        throw ApiTypeException::createForInvalidApiType();
    }
}