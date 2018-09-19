<?php

namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\WebformSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\WebformSettingsFactory;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\GetresponseApiException;
use GrShareCode\WebForm\WebFormCollection;
use GrShareCode\WebForm\WebFormService;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\View\Element\Template;

/**
 * Class Webform
 * @package GetResponse\GetResponseIntegration\Block
 */
class Webform extends Template
{
    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /**
     * @param Context $context
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @internal param GrRepository $grRepository
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
     * @return WebformSettings
     */
    public function getWebformSettings()
    {
        return WebformSettingsFactory::createFromArray(
            $this->repository->getWebformSettings()
        );
    }

    /**
     * @return WebFormCollection
     * @throws ApiTypeException
     * @throws GetresponseApiException
     * @throws RepositoryException
     */
    public function getWebForms()
    {
        $grRepository = $this->repositoryFactory->createGetResponseApiClient();
        return (new WebFormService($grRepository))->getAllWebForms();
    }
}
