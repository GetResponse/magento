<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository as MagentoRepository;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class RepositoryFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class RepositoryFactory
{
    /** @var MagentoRepository */
    private $repository;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CacheInterface */
    private $cache;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param MagentoRepository $repository
     * @param CacheInterface $cache
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        MagentoRepository $repository,
        CacheInterface $cache
    ){
        $this->objectManager = $objectManager;
        $this->repository = $repository;
        $this->cache = $cache;
    }

    /**
     * @return Repository
     * @throws RepositoryException
     */
    public function buildRepository()
    {
        return RepositoryFactory::buildFromConnectionSettings(ConnectionSettingsFactory::buildFromRepository(
            $this->repository->getConnectionSettings()
        ));
    }

    /**
     * @param ConnectionSettings $connectionSettings
     *
     * @return Repository
     */
    public function buildFromConnectionSettings(ConnectionSettings $connectionSettings)
    {
        return $this->createRepository(
            $connectionSettings->getApiKey(),
            $connectionSettings->getUrl(),
            $connectionSettings->getDomain()
        );
    }

    /**
     * @param string $apiKey
     * @param string $url
     * @param string $domain
     *
     * @return Repository
     */
    public function createRepository($apiKey, $url, $domain)
    {
        return new Repository(
            new GetResponseAPI3(
                $apiKey,
                $url,
                $domain,
                $this->getVersion()
            ),
            $this->cache
        );
    }

    private function getVersion()
    {
        $moduleInfo = $this->objectManager->get('Magento\Framework\Module\ModuleList')->getOne('GetResponse_GetResponseIntegration');

        return isset($moduleInfo['setup_version']) ? $moduleInfo['setup_version'] : '';
    }
}
