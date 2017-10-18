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

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return bool
     */
    public function isConnectedToGetResponse()
    {
        $settings = $this->repository->getSettings();

        if (!isset($settings['api_key']) || 0 === strlen($settings['api_key'])) {
            return false;
        }

        return true;
    }
}