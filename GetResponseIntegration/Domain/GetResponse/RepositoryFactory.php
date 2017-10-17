<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use Magento\Framework\ObjectManagerInterface;
use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;

/**
 * Class RepositoryFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class RepositoryFactory
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function buildRepository()
    {
        $storeId = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        $data = $settings->load($storeId, 'id_shop')->getData();

        if (!isset($data['api_key'])) {
            throw GetResponseRepositoryException::buildForInvalidApiKey();
        }

        return new Repository(new GetResponseAPI3(
            $data['api_key'],
            $data['api_url'],
            $data['api_domain'],
            $this->getVersion()
        ));
    }

    public function createRepository($apiKey, $url, $domain)
    {
        return new Repository(new GetResponseAPI3(
            $apiKey,
            $url,
            $domain,
            $this->getVersion()
        ));
    }

    private function getVersion()
    {
        $moduleInfo = $this->objectManager->get('Magento\Framework\Module\ModuleList')->getOne('GetResponse_GetResponseIntegration');

        return isset($moduleInfo['setup_version']) ? $moduleInfo['setup_version'] : '';
    }
}