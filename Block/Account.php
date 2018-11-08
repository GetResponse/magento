<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account as AccountBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\AccountFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\App\Request\Http;

/**
 * Class Account
 * @package GetResponse\GetResponseIntegration\Block
 */
class Account extends Template
{
    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param Repository $repository
     */
    public function __construct(Context $context, Repository $repository)
    {
        parent::__construct($context);
        $this->repository = $repository;
    }

    /**
     * @return AccountBlock
     */
    public function getAccountInfo()
    {
        return AccountFactory::createFromArray($this->repository->getAccountInfo());
    }

    /**
     * @return bool
     */
    public function isConnectedToGetResponse()
    {
        $settings = $this->repository->getConnectionSettings();
        return !empty($settings['apiKey']);
    }

    /**
     * @return string
     */
    public function getLastPostedApiKey()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        if (!empty($data)) {
            if (isset($data['api_key'])) {
                return $data['api_key'];
            }
        }

        return '';
    }

    /**
     * @return int
     */
    public function getLastPostedTypeOfAccountCheckboxValue()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        if (!empty($data['is_mx']) && 1 == $data['is_mx']) {
            return 1;
        }

        return 0;
    }

    /**
     * @return string
     */
    public function getLastPostedApiUrl()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        if (!empty($data['api_url'])) {
            return $data['api_url'];
        }

        return '';
    }

    /**
     * @return string
     */
    public function getLastPostedApiDomain()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        if (!empty($data['getresponse_api_domain'])) {
            return $data['getresponse_api_domain'];
        }

        return '';
    }

    /**
     * @return string
     */
    public function getHiddenApiKey()
    {
        try {
            $connectionSettings = ConnectionSettingsFactory::createFromArray($this->repository->getConnectionSettings());
            return str_repeat("*", strlen($connectionSettings->getApiKey()) - 6) . substr($connectionSettings->getApiKey(), -6);
        } catch (ConnectionSettingsException $e) {
            return '';
        }
    }
}
