<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

/**
 * Class Ecommerce
 * @package GetResponse\GetResponseIntegration\Block
 */
class Ecommerce extends Template
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
     *
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
     * @return string
     */
    public function getShopStatus()
    {
        return $this->repository->getShopStatus();
    }

    /**
     * @return string
     */
    public function getCurrentShopId()
    {
        return $this->repository->getShopId();
    }

    /**
     * @return array
     */
    public function getShops()
    {
        return (array)$this->grRepository->getShops();
    }

    /**
     * @return RegistrationSettings
     */
    public function getRegistrationSettings()
    {
        return $this->getresponseBlock->getRegistrationSettings();
    }
}
