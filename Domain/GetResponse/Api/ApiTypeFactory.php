<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettings;
use GrShareCode\Api\Authorization\ApiTypeException;
use GrShareCode\Api\Authorization\Authorization;

class ApiTypeFactory
{
    /**
     * @param ConnectionSettings $connectionSettings
     * @return string
     * @throws ApiTypeException
     */
    public static function createFromConnectionSettings(
        ConnectionSettings $connectionSettings
    ): string {
        switch ($connectionSettings->getUrl()) {
            case '':
                return Authorization::SMB;
            case Authorization::API_URL_MX_PL:
                return Authorization::MX_PL;
            case Authorization::API_URL_MX_US:
                return Authorization::MX_US;
        }

        throw ApiTypeException::createForInvalidApiType();
    }
}
