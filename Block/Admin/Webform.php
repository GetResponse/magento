<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebForm as WebFormSettings;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Route;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\WebForm\WebFormCollection;
use GrShareCode\WebForm\WebFormService;
use Magento\Framework\View\Element\Template\Context;

class Webform extends AdminTemplate
{
    private $repository;

    public function __construct(
        Context $context,
        Repository $repository,
        MagentoStore $magentoStore,
        ApiClientFactory $apiClientFactory
    ) {
        parent::__construct($context, $magentoStore);

        $this->repository = $repository;
        $this->routePrefix = Route::WEBFORM_INDEX_ROUTE;
        $this->apiClient =  $apiClientFactory->createGetResponseApiClient($this->getScope());
    }

    public function getWebFormSettings(): WebFormSettings
    {
        return WebFormSettings::createFromRepository(
            $this->repository->getWebformSettings($this->getScope()->getScopeId())
        );
    }

    /**
     * @return WebFormCollection
     * @throws GetresponseApiException
     */
    public function getWebForms(): WebFormCollection
    {
        return (new WebFormService($this->apiClient))->getAllWebForms();
    }
}
