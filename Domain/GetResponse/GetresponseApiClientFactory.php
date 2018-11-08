<?php

namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GrShareCode\Api\ApiKeyAuthorization;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Api\UserAgentHeader;
use GrShareCode\GetresponseApi;
use GrShareCode\GetresponseApiClient;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiTypeFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository as MagentoRepository;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class GetresponseApiClientFactory
 * @package ShareCode
 */
class GetresponseApiClientFactory
{
    /** @var MagentoRepository */
    private $repository;

    /** @var ShareCodeRepository */
    private $sharedCodeRepository;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CacheInterface */
    private $cache;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param MagentoRepository $repository
     * @param ShareCodeRepository $sharedCodeRepository
     * @param CacheInterface $cache
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        MagentoRepository $repository,
        ShareCodeRepository $sharedCodeRepository,
        CacheInterface $cache
    ) {
        $this->objectManager = $objectManager;
        $this->repository = $repository;
        $this->sharedCodeRepository = $sharedCodeRepository;
        $this->cache = $cache;
    }

    /**
     * @param $apiKey
     * @param $apiType
     * @param $domain
     * @return GetresponseApiClient
     * @throws ApiTypeException
     */
    public function createFromParams($apiKey, $apiType, $domain)
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
                    $this->repository->getGetResponsePluginVersion()
                )
            ),
            $this->sharedCodeRepository
        );
    }

    /**
     * @return GetresponseApiClient
     * @throws RepositoryException
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
            throw RepositoryException::buildForInvalidApiKey();
        } catch (ApiTypeException $e) {
            throw RepositoryException::buildForInvalidApiKey();
        }
    }

    /**
     * @param ConnectionSettings $settings
     * @return GetresponseApiClient
     * @throws ApiTypeException
     * @throws RepositoryException
     */
    public function createApiClientFromConnectionSettings(ConnectionSettings $settings)
    {
        if (empty($settings->getApiKey())) {
            throw RepositoryException::buildForInvalidApiKey();
        }

        return $this->createFromParams(
            $settings->getApiKey(),
            ApiTypeFactory::createFromConnectionSettings($settings),
            $settings->getDomain()
        );
    }
}
