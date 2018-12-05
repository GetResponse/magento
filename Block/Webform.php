<?php

namespace GetResponse\GetResponseIntegration\Block;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebformSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\WebformSettingsFactory;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\WebForm\WebFormCollection;
use GrShareCode\WebForm\WebFormService;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Webform
 * @package GetResponse\GetResponseIntegration\Block
 */
class Webform extends GetResponse
{
    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @param ApiClientFactory $apiClientFactory
     * @param Logger $logger
     * @param Repository $repository
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        ApiClientFactory $apiClientFactory,
        Logger $logger,
        Repository $repository
    ) {
        parent::__construct(
            $context,
            $messageManager,
            $redirectFactory,
            $apiClientFactory,
            $logger
        );
        $this->repository = $repository;
    }

    /**
     * @return WebformSettings
     */
    public function getWebFormSettings()
    {
        return WebformSettingsFactory::createFromArray(
            $this->repository->getWebformSettings()
        );
    }

    /**
     * @return WebFormCollection|Redirect
     */
    public function getWebForms()
    {
        try {
            return (new WebFormService($this->getApiClientFactory()->createGetResponseApiClient()))->getAllWebForms();
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
