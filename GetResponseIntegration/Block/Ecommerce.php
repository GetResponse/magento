<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettingsFactory;
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

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
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
        return (array) $this->grRepository->getShops();
    }

    /**
     * @return RegistrationSettings
     */
    public function getRegistrationSettings()
    {
        return RegistrationSettingsFactory::createFromRepository(
            $this->repository->getRegistrationSettings()
        );
    }
}
