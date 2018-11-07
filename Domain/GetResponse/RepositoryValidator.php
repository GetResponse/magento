<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository as MagentoRepository;
use GrShareCode\GetresponseApiClient;
use GrShareCode\GetresponseApiException;
use Magento\Framework\Controller\ResultFactory;


/**
 * Class RepositoryValidator
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class RepositoryValidator
{
    /** @var MagentoRepository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /** @var ResultFactory */
    private $resultFactory;

    /**
     * @param RepositoryFactory $repositoryFactory
     * @param MagentoRepository $repository
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        RepositoryFactory $repositoryFactory,
        MagentoRepository $repository,
        ResultFactory $resultFactory
    ) {
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
        $this->resultFactory = $resultFactory;
    }
}
