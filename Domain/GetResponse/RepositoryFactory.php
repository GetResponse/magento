<?php

namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiTypeFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RepositoryForSharedCode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository as MagentoRepository;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\GetresponseApiClient;
use GrShareCode\GetresponseApiClientFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class RepositoryFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class RepositoryFactory
{
    /** @var MagentoRepository */
    private $repository;

    /** @var RepositoryForSharedCode */
    private $sharedCodeRepository;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CacheInterface */
    private $cache;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param MagentoRepository $repository
     * @param RepositoryForSharedCode $sharedCodeRepository
     * @param CacheInterface $cache
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        MagentoRepository $repository,
        RepositoryForSharedCode $sharedCodeRepository,
        CacheInterface $cache
    ) {
        $this->objectManager = $objectManager;
        $this->repository = $repository;
        $this->sharedCodeRepository = $sharedCodeRepository;
        $this->cache = $cache;
    }

    /**
     * @return GetresponseApiClient
     * @throws RepositoryException
     * @throws ApiTypeException
     */
    public function createGetResponseApiClient()
    {
        $settings = ConnectionSettingsFactory::createFromArray(
            $this->repository->getConnectionSettings()
        );

        if (empty($settings->getApiKey())) {
            throw RepositoryException::buildForInvalidApiKey();
        }

        return GetresponseApiClientFactory::createFromParams(
            $settings->getApiKey(),
            ApiTypeFactory::createFromConnectionSettings($settings),
            $settings->getDomain(),
            $this->sharedCodeRepository,
            $this->repository->getGetResponsePluginVersion()
        );
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

        return GetresponseApiClientFactory::createFromParams(
            $settings->getApiKey(),
            ApiTypeFactory::createFromConnectionSettings($settings),
            $settings->getDomain(),
            $this->sharedCodeRepository,
            $this->repository->getGetResponsePluginVersion()
        );
    }
}
