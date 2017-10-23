<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository as MagentoRepository;
use Magento\Framework\ObjectManagerInterface;

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

    /**
     * @param ObjectManagerInterface $objectManager
     * @param MagentoRepository $repository
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        MagentoRepository $repository
    ) {
        $this->objectManager = $objectManager;
        $this->repository = $repository;
    }

    /**
     * @return Repository
     * @throws GetResponseRepositoryException
     */
    public function createRepository()
    {
        $connectionSettings = ConnectionSettingsFactory::createFromArray(
            $this->repository->getConnectionSettings()
        );

        if (empty($connectionSettings->getApiKey())) {
            throw GetResponseRepositoryException::buildForInvalidApiKey();
        }

        return RepositoryFactory::createFromConnectionSettings($connectionSettings);
    }

    /**
     * @param ConnectionSettings $connectionSettings
     *
     * @return Repository
     */
    public function createFromConnectionSettings(ConnectionSettings $connectionSettings)
    {
        return new Repository(new GetResponseAPI3(
            $connectionSettings->getApiKey(),
            $connectionSettings->getUrl(),
            $connectionSettings->getDomain(),
            $this->getVersion()
        ));
    }

    /**
     * @param string $apiKey
     * @param string $url
     * @param string $domain
     *
     * @return Repository
     */
    public function createNewRepository($apiKey, $url, $domain)
    {
        return new Repository(new GetResponseAPI3(
            $apiKey,
            $url,
            $domain,
            $this->getVersion()
        ));
    }

    /**
     * @return string
     */
    private function getVersion()
    {
        $moduleInfo = $this->objectManager->get('Magento\Framework\Module\ModuleList')
            ->getOne('GetResponse_GetResponseIntegration');

        return isset($moduleInfo['setup_version']) ? $moduleInfo['setup_version'] : '';
    }
}
