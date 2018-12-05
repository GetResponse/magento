<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository as MagentoRepository;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GrShareCode\Api\Authorization\ApiKeyAuthorization;
use GrShareCode\Api\Authorization\ApiTypeException;
use GrShareCode\Api\GetresponseApi;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\Api\UserAgentHeader;

/**
 * Class ApiClientFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Api
 */
class ApiClientFactory
{
    /** @var MagentoRepository */
    private $repository;

    /** @var ShareCodeRepository */
    private $sharedCodeRepository;

    /**
     * @param MagentoRepository $repository
     * @param ShareCodeRepository $sharedCodeRepository
     */
    public function __construct(
        MagentoRepository $repository,
        ShareCodeRepository $sharedCodeRepository
    ) {
        $this->repository = $repository;
        $this->sharedCodeRepository = $sharedCodeRepository;
    }

    /**
     * @return GetresponseApiClient
     * @throws ApiException
     */
    public function createGetResponseApiClient()
    {
        try {
            $settings = ConnectionSettingsFactory::createFromArray(
                $this->repository->getConnectionSettings()
            );

            return $this->createFromParams(
                $settings->getApiKey(),
                ApiTypeFactory::createFromConnectionSettings($settings),
                $settings->getDomain()
            );
        } catch (ConnectionSettingsException $e) {
            throw ApiException::buildForInvalidApiKey();
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
    public function createFromParams($apiKey, $apiType, $domain)
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
                    $this->repository->getGetResponsePluginVersion()
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
    public function createApiClientFromConnectionSettings(ConnectionSettings $settings)
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
