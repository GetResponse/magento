<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;

/**
 * Class AccessValidator
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml
 */
class AccessValidator
{
    /** @var Repository */
    private $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return bool
     */
    public function isConnectedToGetResponse()
    {
        $connectionSettings = $this->repository->getConnectionSettings();

        if (empty($connectionSettings)) {
            return false;
        }

        if (!isset($connectionSettings['apiKey']) || 0 === strlen($connectionSettings['apiKey'])) {
            return false;
        }

        return true;
    }
}
