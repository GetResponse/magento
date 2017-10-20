<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\WebformCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\WebformsCollection;
use GetResponse\GetResponseIntegration\Domain\Magento\WebformSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\WebformSettingsFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;
use Magento\Framework\View\Element\Template;

/**
 * Class Webform
 * @package GetResponse\GetResponseIntegration\Block
 */
class Webform extends Template
{
    /** @var Repository */
    private $repository;

    /** @var GrRepository */
    private $grRepository;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @internal param GrRepository $grRepository
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Repository $repository,
        RepositoryFactory $repositoryFactory
    )
    {
        parent::__construct($context);
        $this->repository = $repository;
        $this->grRepository = $repositoryFactory->buildRepository();
    }

    /**
     * @return WebformSettings
     */
    public function getWebformSettings()
    {
        return WebformSettingsFactory::buildFromRepository(
            $this->repository->getWebformSettings()
        );
    }

    /**
     * @return WebformsCollection
     */
    public function getWebFormsCollection()
    {
        return WebformCollectionFactory::buildFromApiResponse(
            (array) $this->grRepository->getForms(['query' => ['status' => 'enabled']]),
            (array) $this->grRepository->getWebForms()
        );
    }
}
