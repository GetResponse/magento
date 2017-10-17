<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Ecommerce
 * @package GetResponse\GetResponseIntegration\Block
 */
class Ecommerce extends GetResponse
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
        parent::__construct($context, $objectManager);
        $this->repository = $repository;
        $this->grRepository = $repositoryFactory->buildRepository();
    }

    /**
     * @return string
     */
    public function getShopStatusFromConfig()
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

    public function getSettings()
    {
        return $this->repository->getSettings();
    }
}
