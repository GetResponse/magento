<?php

namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GrShareCode\Api\ApiKeyAuthorization;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Api\UserAgentHeader;
use GrShareCode\GetresponseApi;
use GrShareCode\GetresponseApiClient;

/**
 * Class GetresponseApiClientFactory
 * @package ShareCode
 */
class GetresponseApiClientFactory
{
    /**
     * @param $apiKey
     * @param $apiType
     * @param $domain
     * @param $sharedCodeRepository
     * @param $pluginVersion
     * @return GetresponseApiClient
     * @throws ApiTypeException
     */
    public static function createFromParams($apiKey, $apiType, $domain, $sharedCodeRepository, $pluginVersion)
    {
        return new GetresponseApiClient(
            $getResponseApiClient = new GetresponseApi(
                new ApiKeyAuthorization(
                    $apiKey,
                    $apiType,
                    $domain
                ),
                Config::X_APP_ID,
                new UserAgentHeader(
                    Config::SERVICE_NAME,
                    Config::SERVICE_VERSION,
                    $pluginVersion
                )
            ),
            $sharedCodeRepository
        );
    }
}
