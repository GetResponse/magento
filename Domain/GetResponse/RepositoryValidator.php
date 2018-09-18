<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository as MagentoRepository;
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

    /**
     * @return bool
     */
    public function validate()
    {
        try {
            return $this->validateGrRepository($this->repositoryFactory->createRepository());
        } catch (RepositoryException $e) {
            return false;
        }
    }

    /**
     * @param GrRepository $grRepository
     *
     * @return bool
     */
    public function validateGrRepository(GrRepository $grRepository)
    {
        $response = $grRepository->ping();

        if (isset($response['httpStatus']) && (int) $response['httpStatus'] >= 400 && (int) $response['httpStatus'] < 500) {
            return false;
        }

        return true;
    }
}
