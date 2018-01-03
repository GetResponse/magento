<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

/**
 * Class Export
 * @package GetResponse\GetResponseIntegration\Block
 */
class Export extends Template
{
    /** @var Repository */
    private $repository;

    /** @var GrRepository */
    private $grRepository;

    /** @var Getresponse */
    private $getresponseBlock;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param Getresponse $getresponseBlock
     * @throws RepositoryException
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        Getresponse $getresponseBlock
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->grRepository = $repositoryFactory->createRepository();
        $this->getresponseBlock = $getresponseBlock;
    }

    /**
     * @return RegistrationSettings
     */
    public function getExportSettings()
    {
        return $this->getresponseBlock->getRegistrationSettings();
    }

    /**
     * @return mixed
     */
    public function getCustomers()
    {
        return $this->repository->getCustomers();
    }

    /**
     * @return CustomFieldsCollection
     */
    public function getCustoms()
    {
        return $this->getresponseBlock->getCustoms();
    }

    /**
     * @return mixed
     */
    public function getCampaigns()
    {
        return $this->grRepository->getCampaigns(['sort' => ['name' => 'asc']]);
    }
}
