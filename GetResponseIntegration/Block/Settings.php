<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\AccountFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Request\Http;

/**
 * Class Settings
 * @package GetResponse\GetResponseIntegration\Block
 */
class Settings extends Template
{
    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /**
     * @param Context $context
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     */
    public function __construct(
        Context $context,
        Repository $repository,
        RepositoryFactory $repositoryFactory
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * @return mixed
     */
    public function getAccountInfo()
    {
        return AccountFactory::createFromArray($this->repository->getAccountInfo());
    }

    /**
     * @return bool
     */
    public function getLastPostedApiKey()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        if (!empty($data)) {
            if (isset($data['getresponse_api_key'])) {
                return $data['getresponse_api_key'];
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getHiddenApiKey()
    {
        $settings = $this->repository->getConnectionSettings();

        if (empty($settings)) {
            return '';
        }

        $settings = ConnectionSettingsFactory::createFromArray($settings);

        if (empty($settings->getApiKey())) {
            return '';
        }

        return strlen($settings->getApiKey()) > 0 ? str_repeat("*",
                strlen($settings->getApiKey()) - 6) . substr($settings->getApiKey(), -6) : '';
    }

    /**
     * @return int
     */
    public function getLastPostedApiAccount()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        if (!empty($data['getresponse_360_account']) && 1 == $data['getresponse_360_account']) {
            return $data['getresponse_360_account'];
        }

        return 0;
    }

    /**
     * @return bool
     */
    public function getLastPostedApiUrl()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        if (!empty($data['getresponse_api_url'])) {
            return $data['getresponse_api_url'];
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getLastPostedApiDomain()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        if (!empty($data['getresponse_api_domain'])) {
            return $data['getresponse_api_domain'];
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isConnectedToGetResponse()
    {
        $settings = $this->repository->getConnectionSettings();

        return !empty($settings['apiKey']);
    }
}
