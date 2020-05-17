<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Api;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\AccountReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GetResponse\GetResponseIntegration\Domain\Magento\Store\ReadModel\StoreReadModel;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GrShareCode\Api\Authorization\ApiKeyAuthorization;
use GrShareCode\Api\Authorization\ApiTypeException;
use GrShareCode\Api\GetresponseApi;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\Api\UserAgentHeader;

class ApiClientFactory
{
    private $sharedCodeRepository;
    private $storeReadModel;
    private $accountReadModel;

    public function __construct(
        ShareCodeRepository $sharedCodeRepository,
        StoreReadModel $storeReadModel,
        AccountReadModel $accountReadModel
    ) {
        $this->sharedCodeRepository = $sharedCodeRepository;
        $this->storeReadModel = $storeReadModel;
        $this->accountReadModel = $accountReadModel;
    }

    /**
     * @param Scope $scope
     * @return GetresponseApiClient
     * @throws ApiException
     */
    public function createGetResponseApiClient(Scope $scope)
    {
        try {
            $settings = $this->accountReadModel->getConnectionSettings($scope);

            return $this->createFromParams(
                $settings->getApiKey(),
                ApiTypeFactory::createFromConnectionSettings($settings),
                $settings->getDomain()
            );
        } catch (ApiTypeException $e) {
            throw ApiException::buildForInvalidApiKey();
        }
    }

    /**
     * @param string $apiKey
     * @param string $apiType
     * @param string $domain
     * @return GetresponseApiClient
     * @throws ApiTypeException
     */
    public function createFromParams($apiKey, $apiType, $domain): GetresponseApiClient
    {
        return new GetresponseApiClient(
            new GetresponseApi(
                new ApiKeyAuthorization(
                    $apiKey,
                    $apiType,
                    $domain
                ),
                Config::X_APP_ID,
                new UserAgentHeader(
                    Config::SERVICE_NAME,
                    Config::SERVICE_VERSION,
                    $this->storeReadModel->getGetResponsePluginVersion()
                )
            ),
            $this->sharedCodeRepository
        );
    }

    /**
     * @param ConnectionSettings $settings
     * @return GetresponseApiClient
     * @throws ApiTypeException
     * @throws ApiException
     */
    public function createApiClientFromConnectionSettings(ConnectionSettings $settings): GetresponseApiClient
    {
        if (empty($settings->getApiKey())) {
            throw ApiException::buildForInvalidApiKey();
        }

        return $this->createFromParams(
            $settings->getApiKey(),
            ApiTypeFactory::createFromConnectionSettings($settings),
            $settings->getDomain()
        );
    }
}
